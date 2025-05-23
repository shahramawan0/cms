<?php

namespace App\Http\Controllers;

use App\Models\StudentEnrollment;
use App\Models\User;
use App\Models\Institute;
use App\Models\Session;
use App\Models\Classes;
use App\Models\Section;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeacherCourseEnrollController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $institutes = [];
        $sessions = [];
        $teachers = [];

        if ($user->hasRole('Super Admin')) {
            $institutes = Institute::all();
        } elseif ($user->hasRole('Admin')) {
            $teachers = User::role('Teacher')->where('institute_id', $user->institute_id)->get();
            $sessions = Session::where('institute_id', $user->institute_id)->get();
        } elseif ($user->hasRole('Teacher')) {
            $sessions = StudentEnrollment::where('institute_id', $user->institute_id)
                ->with('session')
                ->get()
                ->pluck('session')
                ->unique()
                ->values();
        }

        return view('enrollments.teacherCourseEnrollment.index', compact('institutes', 'sessions', 'teachers'));
    }

    public function getSessionData(Request $request)
    {
        try {
            $user = auth()->user();
            $instituteId = $user->institute_id;

            if ($user->hasRole('Super Admin')) {
                if (!$request->institute_id) {
                    return response()->json(['error' => 'Institute ID is required'], 400);
                }
                $instituteId = $request->institute_id;
            }

            if (!$request->session_id) {
                return response()->json(['error' => 'Session ID is required'], 400);
            }

            // Get all courses with their classes and sections from student enrollments
            $courses = StudentEnrollment::where('institute_id', $instituteId)
                ->where('session_id', $request->session_id)
                ->whereNull('teacher_id')
                ->with(['course', 'class', 'section'])
                ->get()
                ->groupBy(['course_id', 'class_id', 'section_id'])
                ->map(function ($courseGroups) {
                    return $courseGroups->map(function ($classGroups) {
                        return $classGroups->map(function ($sectionGroups) {
                            $firstRecord = $sectionGroups->first();
                            return [
                                'course_id' => $firstRecord->course_id,
                                'course_name' => $firstRecord->course->course_name ?? 'N/A',
                                'class_id' => $firstRecord->class_id,
                                'class_name' => $firstRecord->class->name ?? 'N/A',
                                'section_id' => $firstRecord->section_id,
                                'section_name' => $firstRecord->section->section_name ?? 'N/A',
                            ];
                        });
                    });
                })
                ->flatten(2)
                ->values();

            // Get teachers for admin/super admin dropdown
            $teachers = [];
            if ($user->hasRole('Admin') || $user->hasRole('Super Admin')) {
                $teachers = User::role('Teacher')
                    ->where('institute_id', $instituteId)
                    ->get(['id', 'name']);
            }

            return response()->json([
                'success' => true,
                'courses' => $courses,
                'teachers' => $teachers
            ]);

        } catch (\Exception $e) {
            \Log::error("Error in getSessionData: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'teacher_id' => ($user->hasRole('Admin') || $user->hasRole('Super Admin')) ? 'required|exists:users,id' : 'nullable',
            'session_id' => 'required|exists:sessions,id',
            'course_ids' => 'required|array',
            'course_ids.*' => 'exists:courses,id',
            'class_ids' => 'required|array',
            'class_ids.*' => 'exists:classes,id',
            'section_ids' => 'required|array',
            'section_ids.*' => 'exists:sections,id',
        ]);

        DB::beginTransaction();

        try {
            // For teacher, use their own ID if not admin/super admin
            $teacherId = ($user->hasRole('Admin') || $user->hasRole('Super Admin')) ? $request->teacher_id : $user->id;
            $instituteId = $user->institute_id;

            $successCount = 0;
            $errors = [];

            foreach ($request->course_ids as $index => $courseId) {
                $classId = $request->class_ids[$index];
                $sectionId = $request->section_ids[$index];

                // Check if this teacher is already assigned to this course/class/section
                $existingAssignment = StudentEnrollment::where([
                    'institute_id' => $instituteId,
                    'session_id' => $request->session_id,
                    'course_id' => $courseId,
                    'class_id' => $classId,
                    'section_id' => $sectionId,
                    'teacher_id' => $teacherId
                ])->exists();

                if ($existingAssignment) {
                    $errors[] = "Teacher is already assigned to this course";
                    continue;
                }

                // First, check if there are any enrollments to update
                $hasEnrollments = StudentEnrollment::where([
                    'institute_id' => $instituteId,
                    'session_id' => $request->session_id,
                    'course_id' => $courseId,
                    'class_id' => $classId,
                    'section_id' => $sectionId,
                    'teacher_id' => null
                ])->exists();

                if (!$hasEnrollments) {
                    $errors[] = "No student enrollments found to assign teacher for this course";
                    continue;
                }

                // Update all matching student enrollments with teacher_id
                $updated = StudentEnrollment::where([
                    'institute_id' => $instituteId,
                    'session_id' => $request->session_id,
                    'course_id' => $courseId,
                    'class_id' => $classId,
                    'section_id' => $sectionId,
                    'teacher_id' => null
                ])->update([
                    'teacher_id' => $teacherId,
                    'updated_by' => Auth::id()
                ]);

                if ($updated > 0) {
                    $successCount++;
                }
            }

            if ($successCount === 0 && !empty($errors)) {
                throw new \Exception(implode("\n", $errors));
            }

            DB::commit();

            $message = $successCount . ' courses assigned successfully!';
            if (!empty($errors)) {
                $message .= "\nSome assignments failed:\n" . implode("\n", $errors);
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error assigning teacher: ' . $e->getMessage()
            ], 500);
        }
    }

    // Update the getEnrollments method in TeacherCourseEnrollController
public function getEnrollments()
{
    $user = auth()->user();

    // Start with a query that groups by the unique combinations
    $query = StudentEnrollment::select([
            'institute_id',
            'session_id',
            'class_id',
            'section_id',
            'course_id',
            'teacher_id',
            DB::raw('MIN(enrollment_date) as enrollment_date'),
            DB::raw('MIN(status) as status'),
            DB::raw('COUNT(student_id) as student_count')
        ])
        ->with(['teacher', 'institute', 'session', 'class', 'section', 'course'])
        ->whereNotNull('teacher_id')
        ->groupBy([
            'institute_id',
            'session_id',
            'class_id',
            'section_id',
            'course_id',
            'teacher_id'
        ]);

    if ($user->hasRole('Teacher')) {
        $query->where('teacher_id', $user->id);
    } elseif ($user->hasRole('Admin')) {
        $query->where('institute_id', $user->institute_id);
    } elseif ($user->hasRole('Super Admin')) {
        if (request()->has('institute_id')) {
            $query->where('institute_id', request('institute_id'));
        }
    }

    return datatables()->of($query)
        ->addColumn('teacher_name', function($enrollment) {
            return $enrollment->teacher->name ?? 'N/A';
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
        ->addColumn('course', function($enrollment) {
            return optional($enrollment->course)->course_name ?? 'N/A';
        })
        ->addColumn('enrollment_date', function($enrollment) {
            return $enrollment->enrollment_date;
        })
        ->addColumn('student_count', function($enrollment) {
            return $enrollment->student_count;
        })
        ->addColumn('status', function($enrollment) {
            return $enrollment->status === 'active'
                ? '<span class="badge bg-success">Active</span>'
                : ($enrollment->status === 'inactive' 
                    ? '<span class="badge bg-warning">Inactive</span>'
                    : '<span class="badge bg-secondary">Archived</span>');
        })
        ->addColumn('action', function($enrollment) {
            return '<button class="btn btn-sm btn-danger unassign-btn" 
                data-session-id="'.$enrollment->session_id.'"
                data-course-id="'.$enrollment->course_id.'"
                data-class-id="'.$enrollment->class_id.'"
                data-section-id="'.$enrollment->section_id.'"
                data-teacher-id="'.$enrollment->teacher_id.'">
                <i class="fas fa-trash"></i> Unassign
            </button>';
        })
        ->rawColumns(['status', 'action'])
        ->make(true);
}

    public function unassignTeacher(Request $request)
    {
        try {
            $user = auth()->user();
            
            $request->validate([
                'session_id' => 'required|exists:sessions,id',
                'course_id' => 'required|exists:courses,id',
                'class_id' => 'required|exists:classes,id',
                'section_id' => 'required|exists:sections,id',
                'teacher_id' => 'required|exists:users,id',
            ]);

            // Verify teacher belongs to institute if admin/super admin
            if ($user->hasRole('Admin') || $user->hasRole('Super Admin')) {
                $teacher = User::where('id', $request->teacher_id)
                    ->where('institute_id', $user->institute_id)
                    ->firstOrFail();
            }

            // Unassign teacher from all matching enrollments
            $updated = StudentEnrollment::where([
                'institute_id' => $user->institute_id,
                'session_id' => $request->session_id,
                'course_id' => $request->course_id,
                'class_id' => $request->class_id,
                'section_id' => $request->section_id,
                'teacher_id' => $request->teacher_id
            ])->update([
                'teacher_id' => null,
                'updated_by' => Auth::id()
            ]);

            if ($updated === 0) {
                throw new \Exception('No enrollments found to unassign teacher');
            }

            return response()->json([
                'success' => true,
                'message' => 'Teacher unassigned successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error unassigning teacher: ' . $e->getMessage()
            ], 500);
        }
    }
}