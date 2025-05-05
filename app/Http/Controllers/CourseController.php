<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Institute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    public function index()
    {
        $institutes = [];
        
        if (auth()->user()->hasRole('Super Admin')) {
            $institutes = Institute::get();
        }
        
        return view('course.index', compact('institutes'));
    }

    public function getCourses()
    {
        $courses = Course::with('institute');

        if (auth()->user()->hasRole('Admin')) {
            $courses->where('institute_id', auth()->user()->institute_id);
        }

        return datatables()->of($courses)
            ->addColumn('institute', function($course) {
                return $course->institute ? $course->institute->name : 'N/A';
            })
            ->addColumn('duration', function($course) {
                return $course->duration_months ? $course->duration_months.' months' : 'N/A';
            })
            ->addColumn('date_range', function($course) {
                if ($course->start_date && $course->end_date) {
                    return \Carbon\Carbon::parse($course->start_date)->format('M d, Y') . ' - ' . 
                           \Carbon\Carbon::parse($course->end_date)->format('M d, Y');
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
                        <button class="btn btn-sm btn-primary edit-btn me-1" data-id="'.$course->id.'">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="'.$course->id.'">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['institute', 'duration', 'date_range', 'status', 'action'])
            ->make(true);
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
            'is_active' => 'required|boolean',
        ]);

        try {
            $data = $request->all();
            $data['created_by'] = Auth::id();
            
            // Generate course code if not provided
            if (empty($data['course_code'])) {
                $data['course_code'] = Str::upper(Str::substr($data['course_name'], 0, 3)) . '-' . Str::upper(Str::random(3));
            }

            Course::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Course created successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating course: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        $course = Course::findOrFail($id);
        return response()->json($course);
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
            'is_active' => 'required|boolean',
        ]);

        try {
            $data = $request->all();
            $data['updated_by'] = Auth::id();

            $course->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Course updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating course: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $course = Course::findOrFail($id);
            $course->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Course deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting course: ' . $e->getMessage()
            ], 500);
        }
    }
}