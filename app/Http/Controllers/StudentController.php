<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Institute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;
use App\Models\Session;
use App\Models\Classes;
use App\Models\Course;
use App\Models\Section;
use Illuminate\Support\Facades\Auth;
use App\Models\StudentEnrollment;

class StudentController extends Controller
{
    public function index()
    {
        return view('student.index');
    }

    public function getStudents()
    {
        $user = Auth::user();
        
        // First get students from users table
        $query = User::whereHas('roles', function($q) {
            $q->where('name', 'Student');
        });

        // If not super admin, restrict to institute
        if (!$user->roles->pluck('name')->contains('Super Admin')) {
            $query->where('users.institute_id', $user->institute_id);
        }

        // Join with student_enrollments and other tables for filtering
        $query->leftJoin('student_enrollments', 'users.id', '=', 'student_enrollments.student_id')
            ->leftJoin('sessions', 'student_enrollments.session_id', '=', 'sessions.id')
            ->leftJoin('classes', 'student_enrollments.class_id', '=', 'classes.id')
            ->leftJoin('sections', 'student_enrollments.section_id', '=', 'sections.id')
            ->leftJoin('courses', 'student_enrollments.course_id', '=', 'courses.id')
            ->leftJoin('users as teachers', 'student_enrollments.teacher_id', '=', 'teachers.id')
            ->leftJoin('institutes', 'users.institute_id', '=', 'institutes.id')
            ->select([
                'users.*',
                'institutes.name as institute_name',
                'classes.name as class_name',
                'sections.section_name',
                'courses.course_name',
                'teachers.name as teacher_name',
                'student_enrollments.enrollment_date'
            ]);

        // Apply filters if any
        if (request('session_id')) {
            $query->where('student_enrollments.session_id', request('session_id'));
        }
        if (request('class_id')) {
            $query->where('student_enrollments.class_id', request('class_id'));
        }
        if (request('section_id')) {
            $query->where('student_enrollments.section_id', request('section_id'));
        }
        if (request('course_id')) {
            $query->where('student_enrollments.course_id', request('course_id'));
        }
        if (request('teacher_id')) {
            $query->where('student_enrollments.teacher_id', request('teacher_id'));
        }
        if (request('enrollment_date')) {
            $query->whereDate('student_enrollments.enrollment_date', request('enrollment_date'));
        }

        // If any filter is applied, only show students that have enrollments
        if (request('session_id') || request('class_id') || request('section_id') || 
            request('course_id') || request('teacher_id') || request('enrollment_date')) {
            $query->whereNotNull('student_enrollments.id');
        }

        return datatables()->of($query)
            ->addColumn('institute', function($student) {
                return $student->institute ? $student->institute->institute_name : 'N/A';
            })
            ->addColumn('action', function($student) {
                return '
                    <div class="btn-group">
                        <button class="btn btn-sm btn-primary edit-btn me-1" data-id="'.$student->id.'">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="'.$student->id.'">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function getFilterOptions()
    {
        $user = Auth::user();
        $instituteId = $user->institute_id;
        $isSuperAdmin = $user->roles->pluck('name')->contains('Super Admin');

        // Get sessions from student enrollments
        $sessions = StudentEnrollment::select('sessions.*')
            ->join('sessions', 'student_enrollments.session_id', '=', 'sessions.id')
            ->join('users', 'student_enrollments.student_id', '=', 'users.id')
            ->when(!$isSuperAdmin, function($query) use ($instituteId) {
                return $query->where('users.institute_id', $instituteId);
            })
            ->distinct()
            ->get();

        // Get classes from student enrollments
        $classes = StudentEnrollment::select('classes.*')
            ->join('classes', 'student_enrollments.class_id', '=', 'classes.id')
            ->join('users', 'student_enrollments.student_id', '=', 'users.id')
            ->when(!$isSuperAdmin, function($query) use ($instituteId) {
                return $query->where('users.institute_id', $instituteId);
            })
            ->distinct()
            ->get();

        // Get sections from student enrollments
        $sections = StudentEnrollment::select('sections.*')
            ->join('sections', 'student_enrollments.section_id', '=', 'sections.id')
            ->join('users', 'student_enrollments.student_id', '=', 'users.id')
            ->when(!$isSuperAdmin, function($query) use ($instituteId) {
                return $query->where('users.institute_id', $instituteId);
            })
            ->distinct()
            ->get();

        // Get courses from student enrollments
        $courses = StudentEnrollment::select('courses.*')
            ->join('courses', 'student_enrollments.course_id', '=', 'courses.id')
            ->join('users', 'student_enrollments.student_id', '=', 'users.id')
            ->when(!$isSuperAdmin, function($query) use ($instituteId) {
                return $query->where('users.institute_id', $instituteId);
            })
            ->distinct()
            ->get();

        // Get teachers from student enrollments
        $teachers = StudentEnrollment::select('users.*')
            ->join('users', 'student_enrollments.teacher_id', '=', 'users.id')
            ->join('users as students', 'student_enrollments.student_id', '=', 'students.id')
            ->whereHas('teacher', function($q) {
                $q->whereHas('roles', function($q) {
                    $q->where('name', 'Teacher');
                });
            })
            ->when(!$isSuperAdmin, function($query) use ($instituteId) {
                return $query->where('students.institute_id', $instituteId);
            })
            ->distinct()
            ->get();

        return response()->json([
            'sessions' => $sessions,
            'classes' => $classes,
            'sections' => $sections,
            'courses' => $courses,
            'teachers' => $teachers
        ]);
    }

    public function getSectionsByClass(Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = $user->roles->pluck('name')->contains('Super Admin');
        
        $sections = StudentEnrollment::select('sections.*')
            ->join('sections', 'student_enrollments.section_id', '=', 'sections.id')
            ->where('student_enrollments.class_id', $request->class_id)
            ->when(!$isSuperAdmin, function($query) use ($user) {
                return $query->join('classes', 'sections.class_id', '=', 'classes.id')
                    ->join('sessions', 'classes.session_id', '=', 'sessions.id')
                    ->where('sessions.institute_id', $user->institute_id);
            })
            ->distinct()
            ->get();

        return response()->json(['sections' => $sections]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $instituteId = $user->hasRole('Super Admin') ? $request->institute_id : $user->institute_id;

        $request->validate([
            'name' => 'required|string|max:255',
            'father_name' => 'required|string|max:255',
            'cnic' => 'required|string|max:15|unique:users,cnic',
            'roll_number' => 'required|string|max:50|unique:users,roll_number',
            'gender' => 'required|in:Male,Female,Other',
            'dob' => 'required|date',
            'admission_date' => 'required|date',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        try {
            $data = [
                'name' => $request->name,
                'father_name' => $request->father_name,
                'cnic' => $request->cnic,
                'roll_number' => $request->roll_number,
                'gender' => $request->gender,
                'dob' => $request->dob,
                'admission_date' => $request->admission_date,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'password' => Hash::make($request->password),
                'institute_id' => $instituteId,
                'is_active' => true
            ];

            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                $path = $request->file('profile_image')->store('public/student_profile_images');
                $data['profile_image'] = str_replace('public/', '', $path);
            }

            $student = User::create($data);
            $student->assignRole('Student');

            return response()->json([
                'success' => true,
                'message' => 'Student created successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating student: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
{
    $student = User::role('Student')->findOrFail($id);
    
    // Format dates properly for display (they're already in Y-m-d format)
    $student->dob = $student->dob;
    $student->admission_date = $student->admission_date;
    
    // Make sure gender is lowercase to match select options
    $student->gender = ucfirst(strtolower($student->gender));
    
    return response()->json($student);
}

    public function update(Request $request, $id)
    {
        $student = User::role('Student')->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'father_name' => 'required|string|max:255',
            'cnic' => 'required|string|max:15|unique:users,cnic,'.$student->id,
            'roll_number' => 'required|string|max:50|unique:users,roll_number,'.$student->id,
            'gender' => 'required|in:Male,Female,Other',
            'dob' => 'required|date',
            'admission_date' => 'required|date',
            'email' => 'required|email|unique:users,email,'.$student->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        try {
            $data = [
                'name' => $request->name,
                'father_name' => $request->father_name,
                'cnic' => $request->cnic,
                'roll_number' => $request->roll_number,
                'gender' => $request->gender,
                'dob' => $request->dob,
                'admission_date' => $request->admission_date,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
            ];

            if ($request->password) {
                $data['password'] = Hash::make($request->password);
            }

            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                // Delete old image if exists
                if ($student->profile_image) {
                    Storage::delete('public/' . $student->profile_image);
                }
                
                $path = $request->file('profile_image')->store('public/student_profile_images');
                $data['profile_image'] = str_replace('public/', '', $path);
            }

            // Super admin can change institute, admin cannot
            if (auth()->user()->hasRole('Super Admin') && $request->institute_id) {
                $data['institute_id'] = $request->institute_id;
            }

            $student->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Student updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating student: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $student = User::role('Student')->findOrFail($id);
            
            // Delete profile image if exists
            if ($student->profile_image) {
                Storage::delete('public/' . $student->profile_image);
            }
            
            $student->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Student deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting student: ' . $e->getMessage()
            ], 500);
        }
    }
}