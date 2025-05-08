<?php

namespace App\Http\Controllers;

use App\Models\StudentEnrollment;
use App\Models\StudentEnrollCourse;
use App\Models\User;
use App\Models\Institute;
use App\Models\Session;
use App\Models\Classes;
use App\Models\Section;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class StudentEnrollmentController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $institutes = $user->hasRole('Super Admin') 
            ? Institute::get() 
            : null;

        return view('enrollments.studentEnrollment.index', compact('institutes'));
    }

    public function getStudents(Request $request)
    {
        $request->validate([
            'institute_id' => 'required|exists:institutes,id'
        ]);
    
        try {
            $students = User::role('Student')
                ->where('institute_id', $request->institute_id)
                ->get(['id', 'name']);
    
            return response()->json($students);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load students',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getDropdowns(Request $request)
    {
        try {
            $data = [];
            $user = auth()->user();

            $instituteId = $user->hasRole('Super Admin') 
                ? $request->institute_id 
                : $user->institute_id;

            if (!$instituteId) {
                return response()->json(['error' => 'Institute not specified'], 400);
            }

            // Verify institute exists
            Institute::findOrFail($instituteId);

            // Get sessions for the institute
            $data['sessions'] = Session::where('institute_id', $instituteId)->get(['id', 'session_name']);

            // If session_id is provided, fetch classes for that session
            if ($request->has('session_id')) {
                $data['classes'] = Classes::where('session_id', $request->session_id)->get(['id', 'name']);
            }

            // Get courses for the institute
            $data['courses'] = Course::where('institute_id', $instituteId)->get(['id', 'course_name']);

            // If class_id is provided, fetch sections for that class
            if ($request->has('class_id')) {
                $data['sections'] = Section::where('class_id', $request->class_id)->get(['id', 'section_name']);
            }

            return response()->json($data);
        } catch (\Exception $e) {
            \Log::error("Error in getDropdowns: " . $e->getMessage());
            return response()->json([
                'error' => 'Failed to load dropdown data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'institute_id' => 'required|exists:institutes,id',
            'session_id' => 'required|exists:sessions,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'courses' => 'required|array',
            'courses.*' => 'exists:courses,id',
            'enrollment_date' => 'required|date',
            'status' => 'required|in:active,inactive,archived',
        ]);

        DB::beginTransaction();

        try {
            $enrollments = [];
            
            foreach ($request->courses as $courseId) {
                $enrollments[] = StudentEnrollment::create([
                    'student_id' => $request->student_id,
                    'institute_id' => $request->institute_id,
                    'session_id' => $request->session_id,
                    'class_id' => $request->class_id,
                    'section_id' => $request->section_id,
                    'course_id' => $courseId, // Store individual course ID
                    'enrollment_date' => $request->enrollment_date,
                    'status' => $request->status,
                    'created_by' => Auth::id()
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Student enrolled successfully!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error enrolling student: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        try {
            // Get the first enrollment record for this student/session/class/section
            $enrollment = StudentEnrollment::with(['student', 'institute', 'session', 'class', 'section'])
                ->findOrFail($id);
                
            // Get all course IDs for this enrollment group
            $courseIds = StudentEnrollment::where([
                'student_id' => $enrollment->student_id,
                'session_id' => $enrollment->session_id,
                'class_id' => $enrollment->class_id,
                'section_id' => $enrollment->section_id
            ])->pluck('course_id')->toArray();
                
            return response()->json([
                'id' => $enrollment->id,
                'student_id' => $enrollment->student_id,
                'institute_id' => $enrollment->institute_id,
                'session_id' => $enrollment->session_id,
                'class_id' => $enrollment->class_id,
                'section_id' => $enrollment->section_id,
                'enrollment_date' => $enrollment->enrollment_date,
                'status' => $enrollment->status,
                'course_ids' => $courseIds // Return all course IDs for this enrollment
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load enrollment',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    public function getEnrolledCourses($enrollmentId)
    {
        try {
            $courses = StudentEnrollCourse::where('st_enroll_id', $enrollmentId)
                ->get(['course_id']);
                
                
            return response()->json($courses);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load enrolled courses',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'institute_id' => 'required|exists:institutes,id',
            'session_id' => 'required|exists:sessions,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'courses' => 'required|array',
            'courses.*' => 'exists:courses,id',
            'enrollment_date' => 'required|date',
            'status' => 'required|in:active,inactive,archived',
        ]);

        DB::beginTransaction();

        try {
            // Get the existing enrollment to find all related records
            $existingEnrollment = StudentEnrollment::findOrFail($id);
            
            // Get all existing enrollment IDs for this student/session/class/section
            $existingEnrollments = StudentEnrollment::where([
                'student_id' => $existingEnrollment->student_id,
                'session_id' => $existingEnrollment->session_id,
                'class_id' => $existingEnrollment->class_id,
                'section_id' => $existingEnrollment->section_id
            ])->get();
            
            // Get existing course IDs
            $existingCourseIds = $existingEnrollments->pluck('course_id')->toArray();
            $newCourseIds = $request->courses;
            
            // Courses to add
            $coursesToAdd = array_diff($newCourseIds, $existingCourseIds);
            foreach ($coursesToAdd as $courseId) {
                StudentEnrollment::create([
                    'student_id' => $request->student_id,
                    'institute_id' => $request->institute_id,
                    'session_id' => $request->session_id,
                    'class_id' => $request->class_id,
                    'section_id' => $request->section_id,
                    'course_id' => $courseId,
                    'enrollment_date' => $request->enrollment_date,
                    'status' => $request->status,
                    'updated_by' => Auth::id()
                ]);
            }
            
            // Courses to remove
            $coursesToRemove = array_diff($existingCourseIds, $newCourseIds);
            if (!empty($coursesToRemove)) {
                StudentEnrollment::where([
                    'student_id' => $request->student_id,
                    'session_id' => $request->session_id,
                    'class_id' => $request->class_id,
                    'section_id' => $request->section_id
                ])->whereIn('course_id', $coursesToRemove)->delete();
            }
            
            // Update common fields for all remaining enrollments
            StudentEnrollment::where([
                'student_id' => $request->student_id,
                'session_id' => $request->session_id,
                'class_id' => $request->class_id,
                'section_id' => $request->section_id
            ])->update([
                'enrollment_date' => $request->enrollment_date,
                'status' => $request->status,
                'updated_by' => Auth::id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Enrollment updated successfully!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating enrollment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getEnrollments()
    {
        $user = auth()->user();

        // Group enrollments by student/session/class/section
        $query = StudentEnrollment::with(['student', 'institute', 'session', 'class', 'section'])
            ->select('student_id', 'institute_id', 'session_id', 'class_id', 'section_id')
            ->groupBy('student_id', 'institute_id', 'session_id', 'class_id', 'section_id');

        if ($user->hasRole('Admin')) {
            $query->where('institute_id', $user->institute_id);
        }

        return datatables()->of($query)
            ->addColumn('student_name', function($enrollment) {
                return $enrollment->student->name ?? 'N/A';
            })
            ->addColumn('institute', function($enrollment) {
                return $enrollment->institute->name ?? 'N/A';
            })
            ->addColumn('session', function($enrollment) {
                return optional($enrollment->session)->session_name ?? 'N/A';
            })
            ->addColumn('class', function($enrollment) {
                return optional($enrollment->class)->name ?? 'N/A';
            })
            ->addColumn('section', function($enrollment) {
                return optional($enrollment->section)->section_name ?? 'N/A';
            })
            ->addColumn('courses', function($enrollment) {
                // Get all courses for this enrollment group
                $courses = Course::join('student_enrollments', 'courses.id', '=', 'student_enrollments.course_id')
                    ->where([
                        'student_enrollments.student_id' => $enrollment->student_id,
                        'student_enrollments.session_id' => $enrollment->session_id,
                        'student_enrollments.class_id' => $enrollment->class_id,
                        'student_enrollments.section_id' => $enrollment->section_id
                    ])
                    ->pluck('courses.course_name')
                    ->toArray();
                
                return implode(', ', $courses);
            })
            ->addColumn('enrollment_date', function($enrollment) {
                // Get the enrollment date from any of the records (they should be the same)
                return StudentEnrollment::where([
                    'student_id' => $enrollment->student_id,
                    'session_id' => $enrollment->session_id,
                    'class_id' => $enrollment->class_id,
                    'section_id' => $enrollment->section_id
                ])->value('enrollment_date');
            })
            ->addColumn('status', function($enrollment) {
                // Get the status from any of the records (they should be the same)
                $status = StudentEnrollment::where([
                    'student_id' => $enrollment->student_id,
                    'session_id' => $enrollment->session_id,
                    'class_id' => $enrollment->class_id,
                    'section_id' => $enrollment->section_id
                ])->value('status');
                
                return $status === 'active'
                    ? '<span class="badge bg-success">Active</span>'
                    : ($status === 'inactive' 
                        ? '<span class="badge bg-warning">Inactive</span>'
                        : '<span class="badge bg-secondary">Archived</span>');
            })
            ->addColumn('action', function($enrollment) {
                // Get any ID from the group to use for edit/delete
                $id = StudentEnrollment::where([
                    'student_id' => $enrollment->student_id,
                    'session_id' => $enrollment->session_id,
                    'class_id' => $enrollment->class_id,
                    'section_id' => $enrollment->section_id
                ])->value('id');
                
                return '
                    <div class="btn-group">
                        <button class="btn btn-sm btn-primary edit-btn me-1" data-id="'.$id.'">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="'.$id.'">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }


    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            // Get the enrollment to find all related records
            $enrollment = StudentEnrollment::findOrFail($id);
            
            // Delete all enrollments for this student/session/class/section
            StudentEnrollment::where([
                'student_id' => $enrollment->student_id,
                'session_id' => $enrollment->session_id,
                'class_id' => $enrollment->class_id,
                'section_id' => $enrollment->section_id
            ])->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Enrollment deleted successfully!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error deleting enrollment: ' . $e->getMessage()
            ], 500);
        }
    }
}