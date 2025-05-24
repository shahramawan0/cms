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
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

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

    public function form()
    {
        $user = auth()->user();
        $today = now();
        $institutes = [];
        $sessions = [];
        $teachers = [];
        $activeSession = null;

        if ($user->hasRole('Super Admin')) {
            $institutes = Institute::all();
            $sessions = Session::all();
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

        // Find the active session
        $activeSession = $sessions->first(function($session) use ($today) {
            $startDate = \Carbon\Carbon::parse($session->start_date);
            $endDate = \Carbon\Carbon::parse($session->end_date);
            
            // Check if today falls between start and end date
            $isWithinDateRange = $today->between($startDate, $endDate);
            
            if ($isWithinDateRange) {
                // Check if there's another active session with earlier start date
                $earlierActiveSession = Session::where('id', '!=', $session->id)
                    ->where('institute_id', $session->institute_id)
                    ->where('start_date', '<=', $today)
                    ->where('end_date', '>=', $today)
                    ->where('start_date', '<', $session->start_date)
                    ->exists();
                
                return !$earlierActiveSession;
            }
            
            return false;
        });

        return view('enrollments.teacherCourseEnrollment.form', compact('institutes', 'sessions', 'teachers', 'activeSession'));
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
            $enrollments = StudentEnrollment::where('institute_id', $instituteId)
                ->where('session_id', $request->session_id)
                ->whereNull('teacher_id')
                ->with(['course', 'class', 'section'])
                ->get()
                ->groupBy(['class_id', 'section_id']);

            $formattedData = [];
            
            foreach ($enrollments as $classId => $classSections) {
                $class = Classes::find($classId);
                
                foreach ($classSections as $sectionId => $sectionEnrollments) {
                    $section = Section::find($sectionId);
                    $firstEnrollment = $sectionEnrollments->first();
                    
                    // Get total unique students for this class-section combination
                    $studentCount = StudentEnrollment::where([
                        'institute_id' => $instituteId,
                        'session_id' => $request->session_id,
                        'class_id' => $classId,
                        'section_id' => $sectionId
                    ])
                    ->distinct('student_id')
                    ->count('student_id');

                    // Get all courses for this class-section combination
                    $courses = $sectionEnrollments->map(function ($enrollment) {
                        return [
                            'course_id' => $enrollment->course_id,
                            'course_name' => $enrollment->course->course_name ?? 'N/A'
                        ];
                    })->unique('course_id')->values();

                    $formattedData[] = [
                        'class_id' => $classId,
                        'class_name' => $class->name ?? 'N/A',
                        'background_color' => $class->background_color ?? '#3490dc',
                        'section_id' => $sectionId,
                        'section_name' => $section->section_name ?? 'N/A',
                        'student_count' => $studentCount,
                        'courses' => $courses
                    ];
                }
            }

            // Get teachers for admin/super admin dropdown
            $teachers = [];
            if ($user->hasRole('Admin') || $user->hasRole('Super Admin')) {
                $teachers = User::role('Teacher')
                    ->where('institute_id', $instituteId)
                    ->get(['id', 'name']);
            }

            return response()->json([
                'success' => true,
                'courses' => $formattedData,
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
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
        ]);

        DB::beginTransaction();

        try {
            // For teacher, use their own ID if not admin/super admin
            $teacherId = ($user->hasRole('Admin') || $user->hasRole('Super Admin')) ? $request->teacher_id : $user->id;
            $instituteId = $user->institute_id;

            // Update all matching enrollments with teacher_id
            $updated = StudentEnrollment::where([
                'institute_id' => $instituteId,
                'session_id' => $request->session_id,
                'class_id' => $request->class_id,
                'section_id' => $request->section_id
            ])
            ->whereIn('course_id', $request->course_ids)
            ->whereNull('teacher_id')
            ->update([
                'teacher_id' => $teacherId,
                'updated_by' => Auth::id()
            ]);

            if ($updated === 0) {
                throw new \Exception('No enrollments found to assign teacher');
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $updated . ' enrollments updated successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error assigning teacher: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getEnrollments()
{
    $user = auth()->user();

    $query = StudentEnrollment::select([
            'student_enrollments.institute_id',
            'student_enrollments.session_id',
            'student_enrollments.class_id',
            'student_enrollments.section_id',
            'student_enrollments.course_id',
            'student_enrollments.teacher_id',
            DB::raw('MIN(student_enrollments.enrollment_date) as enrollment_date'),
            DB::raw('MIN(student_enrollments.status) as status'),
            DB::raw('COUNT(DISTINCT student_enrollments.student_id) as student_count'),
            'users.name as teacher_name'
        ])
        ->join('users', 'student_enrollments.teacher_id', '=', 'users.id')
        ->with(['institute', 'session', 'class', 'section', 'course'])
        ->whereNotNull('student_enrollments.teacher_id')
        ->groupBy([
            'student_enrollments.institute_id',
            'student_enrollments.session_id',
            'student_enrollments.class_id',
            'student_enrollments.section_id',
            'student_enrollments.course_id',
            'student_enrollments.teacher_id',
            'users.name'
        ])
        ->orderBy('users.name', 'asc'); // ordering directly to avoid SQL error

    // Role-based filtering
    if ($user->hasRole('Teacher')) {
        $query->where('student_enrollments.teacher_id', $user->id);
    } elseif ($user->hasRole('Admin')) {
        $query->where('student_enrollments.institute_id', $user->institute_id);
    } elseif ($user->hasRole('Super Admin')) {
        if (request()->has('institute_id')) {
            $query->where('student_enrollments.institute_id', request('institute_id'));
        }
    }

    return datatables()->of($query)
        ->addColumn('teacher_name', function($enrollment) {
            return $enrollment->teacher_name ?? 'N/A';
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
                'course_ids' => 'required|array',
                'course_ids.*' => 'exists:courses,id',
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

            // Unassign teacher from specified courses
            $updated = StudentEnrollment::where([
                'institute_id' => $user->institute_id,
                'session_id' => $request->session_id,
                'class_id' => $request->class_id,
                'section_id' => $request->section_id,
                'teacher_id' => $request->teacher_id
            ])
            ->whereIn('course_id', $request->course_ids)
            ->update([
                'teacher_id' => null,
                'updated_by' => Auth::id()
            ]);

            if ($updated === 0) {
                throw new \Exception('No enrollments found to unassign teacher');
            }

            return response()->json([
                'success' => true,
                'message' => 'Teacher unassigned successfully from selected courses!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error unassigning teacher: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getAssignedData(Request $request)
    {
        try {
            $user = auth()->user();
            $instituteId = $user->institute_id;

            if ($user->hasRole('Super Admin') && $request->institute_id) {
                $instituteId = $request->institute_id;
            }

            $query = StudentEnrollment::where('institute_id', $instituteId)
                ->whereNotNull('teacher_id');

            if ($request->session_id) {
                $query->where('session_id', $request->session_id);
            }

            if ($user->hasRole('Teacher')) {
                $query->where('teacher_id', $user->id);
            }

            // Get unique combinations of class, section, and teacher
            $assignments = $query->select([
                'class_id',
                'section_id',
                'teacher_id',
                'session_id',
                DB::raw('COUNT(DISTINCT student_id) as student_count')
            ])
            ->with(['teacher:id,name', 'class:id,name,background_color', 'section:id,section_name'])
            ->groupBy(['class_id', 'section_id', 'teacher_id', 'session_id'])
            ->get();

            $formattedAssignments = [];

            foreach ($assignments as $assignment) {
                // Get all courses for this class-section combination
                $allCourses = Course::whereExists(function ($query) use ($assignment) {
                    $query->select(DB::raw(1))
                        ->from('student_enrollments')
                        ->whereColumn('courses.id', 'student_enrollments.course_id')
                        ->where('student_enrollments.class_id', $assignment->class_id)
                        ->where('student_enrollments.section_id', $assignment->section_id)
                        ->where('student_enrollments.session_id', $assignment->session_id);
                })->get();

                // Get assigned courses for this teacher
                $assignedCourseIds = StudentEnrollment::where([
                    'class_id' => $assignment->class_id,
                    'section_id' => $assignment->section_id,
                    'teacher_id' => $assignment->teacher_id,
                    'session_id' => $assignment->session_id
                ])
                ->pluck('course_id')
                ->unique()
                ->values()
                ->toArray();

                $formattedAssignments[] = [
                    'class_id' => $assignment->class_id,
                    'class_name' => $assignment->class->name ?? 'N/A',
                    'background_color' => $assignment->class->background_color ?? '#3490dc',
                    'section_id' => $assignment->section_id,
                    'section_name' => $assignment->section->section_name ?? 'N/A',
                    'teacher_id' => $assignment->teacher_id,
                    'teacher_name' => $assignment->teacher->name ?? 'N/A',
                    'student_count' => $assignment->student_count,
                    'all_courses' => $allCourses,
                    'assigned_courses' => $assignedCourseIds,
                    'is_editing' => false
                ];
            }

            return response()->json([
                'success' => true,
                'assignments' => $formattedAssignments
            ]);

        } catch (\Exception $e) {
            Log::error("Error in getAssignedData: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load assignments: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'session_id' => 'required|exists:sessions,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'course_ids' => 'required|array',
            'course_ids.*' => 'exists:courses,id',
        ]);

        DB::beginTransaction();

        try {
            $user = auth()->user();
            $instituteId = $user->institute_id;

            // Remove teacher from unselected courses
            StudentEnrollment::where([
                'institute_id' => $instituteId,
                'session_id' => $request->session_id,
                'class_id' => $request->class_id,
                'section_id' => $request->section_id,
                'teacher_id' => $request->teacher_id
            ])
            ->whereNotIn('course_id', $request->course_ids)
            ->update([
                'teacher_id' => null,
                'updated_by' => Auth::id()
            ]);

            // Assign teacher to selected courses
            StudentEnrollment::where([
                'institute_id' => $instituteId,
                'session_id' => $request->session_id,
                'class_id' => $request->class_id,
                'section_id' => $request->section_id
            ])
            ->whereIn('course_id', $request->course_ids)
            ->whereNull('teacher_id')
            ->update([
                'teacher_id' => $request->teacher_id,
                'updated_by' => Auth::id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Courses updated successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating courses: ' . $e->getMessage()
            ], 500);
        }
    }
}