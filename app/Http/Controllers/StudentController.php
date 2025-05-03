<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Course;
use App\Models\Institute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;

class StudentController extends Controller
{
    public function index()
    {
        return view('student.index');
    }

    public function create()
    {
        $institutes = $teachers = $courses = [];
        
        if (auth()->user()->hasRole('Super Admin')) {
            $institutes = Institute::where('is_active', true)->get();
        } 
        elseif (auth()->user()->hasRole('Admin')) {
            $teachers = User::where('institute_id', auth()->user()->institute_id)
                          ->role('Teacher')
                          ->get();
            $courses = Course::where('institute_id', auth()->user()->institute_id)
                          ->where('is_active', true)
                          ->get();
        }
        elseif (auth()->user()->hasRole('Teacher')) {
            $teachers = User::where('id', auth()->id())->get(); // Only show themselves
            $courses = Course::where('institute_id', auth()->user()->institute_id)
                          ->where('is_active', true)
                          ->get();
        }

        return view('student.create', compact('institutes', 'teachers', 'courses'));
    }

    // AJAX methods for dropdowns
    public function getInstituteAdmins($institute_id)
    {
        $admins = User::where('institute_id', $institute_id)
                    ->role('Admin')
                    ->get();
        return response()->json(['admins' => $admins]);
    }

    public function getInstituteTeachers($institute_id)
    {
        $teachers = User::where('institute_id', $institute_id)
                      ->role('Teacher')
                      ->get();
        return response()->json(['teachers' => $teachers]);
    }

    public function getInstituteCourses($institute_id)
    {
        $courses = Course::where('institute_id', $institute_id)
                       ->where('is_active', true)
                       ->get();
        return response()->json(['courses' => $courses]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'gender' => 'required|in:male,female,other',
            'dob' => 'required|date',
            'address' => 'nullable|string',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            
            // Student-specific fields
            'roll_number' => 'nullable|string|max:50',
            'class' => 'nullable|string|max:50',
            'section' => 'nullable|string|max:50',
            'admission_date' => 'required|date',
            
            // Conditional validation based on role
            'institute_id' => auth()->user()->hasRole('Super Admin') ? 'required|exists:institutes,id' : 'nullable',
            'admin_id' => auth()->user()->hasRole('Super Admin') ? 'required|exists:users,id' : 'nullable',
            'teacher_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
        ]);

        $data = $request->except('profile_image', 'password', 'course_id');
        $data['password'] = Hash::make($request->password);
        $data['created_by'] = Auth::id();
        
        // Set institute and admin based on user role
        if (auth()->user()->hasRole('Admin')) {
            $data['institute_id'] = auth()->user()->institute_id;
            $data['admin_id'] = auth()->user()->id;
        } 
        elseif (auth()->user()->hasRole('Teacher')) {
            $data['institute_id'] = auth()->user()->institute_id;
            $data['admin_id'] = auth()->user()->admin_id;
            $data['teacher_id'] = auth()->user()->id;
        }

        if ($request->hasFile('profile_image')) {
            $data['profile_image'] = $request->file('profile_image')->store('users/profile_images', 'public');
        }

        $student = User::create($data);
        $student->assignRole('Student');
        
        // Enroll student in course
        $student->courses()->attach($request->course_id);

        return response()->json([
            'success' => true,
            'message' => 'Student created successfully.',
            'redirect' => route('admin.students.index')
        ]);
    }

    public function edit($id)
    {
        $student = User::with(['courses'])->findOrFail($id);
        
        // Get institutes based on current user role
        if (auth()->user()->hasRole('Super Admin')) {
            $institutes = Institute::where('is_active', true)->get();
        } else {
            $institutes = Institute::where('id', auth()->user()->institute_id)
                            ->where('is_active', true)
                            ->get();
        }

        // Get teachers based on current user role
        if (auth()->user()->hasRole('Super Admin')) {
            $teachers = User::where('institute_id', $student->institute_id)
                         ->role('Teacher')
                         ->get();
        } elseif (auth()->user()->hasRole('Admin')) {
            $teachers = User::where('institute_id', auth()->user()->institute_id)
                         ->role('Teacher')
                         ->get();
        } else {
            $teachers = User::where('id', auth()->id())->get();
        }

        // Get courses
        $courses = Course::where('institute_id', $student->institute_id)
                      ->where('is_active', true)
                      ->get();

        return view('student.create', compact('student', 'institutes', 'teachers', 'courses'));
    }

    public function update(Request $request, $id)
    {
        $student = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$student->id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'gender' => 'required|in:male,female,other',
            'dob' => 'required|date',
            'address' => 'nullable|string',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            
            // Student-specific fields
            'roll_number' => 'nullable|string|max:50',
            'class' => 'nullable|string|max:50',
            'section' => 'nullable|string|max:50',
            'admission_date' => 'required|date',
            
            'teacher_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
        ]);

        $data = $request->except('profile_image', 'password', 'course_id');
        $data['updated_by'] = Auth::id();

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('profile_image')) {
            if ($student->profile_image) {
                Storage::disk('public')->delete($student->profile_image);
            }
            $data['profile_image'] = $request->file('profile_image')->store('users/profile_images', 'public');
        }

        $student->update($data);
        
        // Update course enrollment
        $student->courses()->sync([$request->course_id]);

        return response()->json([
            'success' => true,
            'message' => 'Student updated successfully.',
            'redirect' => route('admin.students.index')
        ]);
    }

    public function destroy($id)
    {
        $student = User::findOrFail($id);
        
        if ($student->profile_image) {
            Storage::disk('public')->delete($student->profile_image);
        }
        
        $student->delete();
        
        return response()->json(['success' => 'Student deleted successfully.']);
    }

    public function view($id)
    {
        $student = User::with(['institute', 'admin', 'teacher', 'courses'])->findOrFail($id);
        return view('student.view', compact('student'));
    }

    public function getStudents()
    {
        $query = User::role('Student')->with(['institute', 'admin', 'teacher', 'courses']);

        if (auth()->user()->hasRole('Super Admin')) {
            // Super Admin sees all students
        } 
        elseif (auth()->user()->hasRole('Admin')) {
            $query->where('institute_id', auth()->user()->institute_id);
        }
        elseif (auth()->user()->hasRole('Teacher')) {
            $query->where('teacher_id', auth()->user()->id);
        }

        return DataTables::of($query)
            ->addColumn('profile_image', function($student) {
                return $student->profile_image 
                    ? '<img src="'.asset('storage/'.$student->profile_image).'" width="50" height="50" class="rounded-circle">'
                    : '<div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px;">
                        <i class="fas fa-user text-white"></i>
                      </div>';
            })
            ->addColumn('roll_number', function($student) {
                return $student->roll_number ?? 'N/A';
            })
            ->addColumn('class_section', function($student) {
                return ($student->class ?? 'N/A') . ' / ' . ($student->section ?? 'N/A');
            })
            ->addColumn('institute', function($student) {
                return $student->institute ? $student->institute->name : 'N/A';
            })
            ->addColumn('admin', function($student) {
                return $student->admin ? $student->admin->name : 'N/A';
            })
            ->addColumn('teacher', function($student) {
                return $student->teacher ? $student->teacher->name : 'N/A';
            })
            ->addColumn('courses', function($student) {
                return $student->courses->pluck('course_name')->join(', ');
            })
            ->addColumn('admission_date', function($student) {
                return $student->admission_date ? $student->admission_date->format('M d, Y') : 'N/A';
            })
            ->addColumn('action', function($student) {
                return '
                    <div class="btn-group">
                        <a href="'.route('admin.students.edit', $student->id).'" class="btn btn-sm btn-info me-1">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <button class="btn btn-sm btn-danger delete-btn me-1" data-id="'.$student->id.'">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                        <a href="'.route('admin.students.view', $student->id).'" class="btn btn-sm btn-secondary me-1">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </div>
                ';
            })
            ->rawColumns(['profile_image', 'action'])
            ->make(true);
    }
}