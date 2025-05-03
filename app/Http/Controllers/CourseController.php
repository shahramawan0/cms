<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Institute;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class CourseController extends Controller
{
    public function index()
    {
        return view('course.index');
    }

    public function create()
    {
        // Get institutes based on current user role
        if (auth()->user()->hasRole('Super Admin')) {
            $institutes = Institute::where('is_active', true)->get();
        } else {
            $institutes = Institute::where('id', auth()->user()->institute_id)
                            ->where('is_active', true)
                            ->get();
        }

        return view('course.create', compact('institutes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'institute_id' => 'required|exists:institutes,id',
            'course_name' => 'required|string|max:255',
            'course_code' => 'nullable|string|max:50|unique:courses,course_code',
            'description' => 'nullable|string',
            'book_name' => 'nullable|string|max:255',
            'level' => 'required|in:Beginner,Intermediate,Advanced',
            'language' => 'required|in:Urdu,Arabic,English,Urdu/English',
            'duration_months' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'boolean',
        ]);

        $data = $request->all();
        $data['created_by'] = Auth::id();
        
        // Generate course code if not provided
        if (empty($data['course_code'])) {
            $data['course_code'] = Str::upper(Str::substr($data['course_name'], 0, 3)) . '-' . Str::upper(Str::random(3));
        }

        Course::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Course created successfully.',
            'redirect' => route('admin.courses.index')
        ]);
    }

    public function edit($id)
    {
        $course = Course::findOrFail($id);
        
        // Get institutes based on current user role
        if (auth()->user()->hasRole('Super Admin')) {
            $institutes = Institute::where('is_active', true)->get();
        } else {
            $institutes = Institute::where('id', auth()->user()->institute_id)
                            ->where('is_active', true)
                            ->get();
        }

        return view('course.create', compact('course', 'institutes'));
    }

    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        $request->validate([
            'institute_id' => 'required|exists:institutes,id',
            'course_name' => 'required|string|max:255',
            'course_code' => 'nullable|string|max:50|unique:courses,course_code,'.$course->id,
            'description' => 'nullable|string',
            'book_name' => 'nullable|string|max:255',
            'level' => 'required|in:Beginner,Intermediate,Advanced',
            'language' => 'required|in:Urdu,Arabic,English,Urdu/English',
            'duration_months' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'boolean',
        ]);

        $data = $request->all();
        $data['updated_by'] = Auth::id();

        $course->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Course updated successfully.',
            'redirect' => route('admin.courses.index')
        ]);
    }

    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        $course->delete();
        
        return response()->json(['success' => 'Course deleted successfully.']);
    }

    public function view($id)
    {
        $course = Course::with('institute')->findOrFail($id);
        return view('course.view', compact('course'));
    }

    public function getCourses()
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    
        // $query = Course::with(['institute', 'creator', 'updater']);
        $query = Course::with('institute');

    
        if (auth()->user()->hasRole('Super Admin')) {
            // Super Admin can see all courses
        } elseif (auth()->user()->hasRole('Admin')) {
            // Admin can only see courses from their institute
            $query->where('institute_id', auth()->user()->institute_id);
        } else {
            // Other roles can't access this
            return response()->json(['error' => 'Forbidden'], 403);
        }
    
        return DataTables::of($query)
            ->addColumn('institute', function($course) {
                return $course->institute ? $course->institute->name : 'N/A';
            })
           
            ->addColumn('duration', function($course) {
                return $course->duration_months ? $course->duration_months.' months' : 'N/A';
            })
            ->addColumn('date_range', function($course) {
                if ($course->start_date && $course->end_date) {
                    return \Carbon\Carbon::parse($course->start_date)->format('M d, Y') . ' - ' . \Carbon\Carbon::parse($course->end_date)->format('M d, Y');
                }
                return 'N/A';
            })
            
            ->addColumn('status', function($course) {
                return $course->is_active 
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>';
            })
            ->addColumn('action', function($course) {
                return '
                    <div class="btn-group">
                        <a href="'.route('admin.courses.edit', $course->id).'" class="btn btn-sm btn-info me-1">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <button class="btn btn-sm btn-danger delete-btn me-1" data-id="'.$course->id.'">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                        <a href="'.route('admin.courses.view', $course->id).'" class="btn btn-sm btn-secondary me-1">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </div>
                ';
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }
}