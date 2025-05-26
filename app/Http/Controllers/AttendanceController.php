<?php

namespace App\Http\Controllers;

use App\Models\StudentEnrollment;
use App\Models\Attendance;
use App\Models\Institute;
use App\Models\TimeSlot;
use App\Models\TimeTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;




class AttendanceController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $institutes = $user->hasRole('Super Admin') ? Institute::get() : null;
        
        // Set timezone to Asia/Karachi
        date_default_timezone_set('Asia/Karachi');
        
        return view('attendances.new_index', compact('institutes'));
    }
    public function getDropdowns(Request $request)
    {
        try {
            $data = [];
            $user = auth()->user();

            $instituteId = $user->hasRole('Super Admin') ? $request->institute_id : $user->institute_id;

            if (!$instituteId) {
                return response()->json(['error' => 'Institute not specified'], 400);
            }

            // If the user is a Student, we'll filter by their enrollments
            $studentId = $user->hasRole('Student') ? $user->id : $request->student_id;

            // Sessions
            $sessionQuery = StudentEnrollment::with('session')
                ->where('institute_id', $instituteId);

            if ($user->hasRole('Teacher')) {
                $sessionQuery->where('teacher_id', $user->id);
            } elseif ($user->hasRole('Student') || $request->has('student_id')) {
                $sessionQuery->where('student_id', $studentId);
            }

            $data['sessions'] = $sessionQuery->select('session_id')
                ->distinct()
                ->get()
                ->pluck('session')
                ->filter()
                ->map(fn($session) => [
                    'id' => $session->id,
                    'session_name' => $session->session_name,
                    'start_date' => $session->start_date ? date('Y-m-d', strtotime($session->start_date)) : null,
                    'end_date' => $session->end_date ? date('Y-m-d', strtotime($session->end_date)) : null
                ])
                ->values();

            // Classes
            if ($request->has('session_id')) {
                $classQuery = StudentEnrollment::with('class')
                    ->where('institute_id', $instituteId)
                    ->where('session_id', $request->session_id);

                if ($user->hasRole('Teacher')) {
                    $classQuery->where('teacher_id', $user->id);
                } elseif ($user->hasRole('Student') || $request->has('student_id')) {
                    $classQuery->where('student_id', $studentId);
                }

                $data['classes'] = $classQuery->select('class_id')
                    ->distinct()
                    ->get()
                    ->pluck('class')
                    ->filter()
                    ->map(fn($class) => [
                        'id' => $class->id,
                        'name' => $class->name
                    ])
                    ->values();
            }

            // Sections
            if ($request->has('class_id')) {
                $sectionQuery = StudentEnrollment::with('section')
                    ->where('institute_id', $instituteId)
                    ->where('session_id', $request->session_id)
                    ->where('class_id', $request->class_id);

                if ($user->hasRole('Teacher')) {
                    $sectionQuery->where('teacher_id', $user->id);
                } elseif ($user->hasRole('Student') || $request->has('student_id')) {
                    $sectionQuery->where('student_id', $studentId);
                }

                $data['sections'] = $sectionQuery->select('section_id')
                    ->distinct()
                    ->get()
                    ->pluck('section')
                    ->filter()
                    ->map(fn($section) => [
                        'id' => $section->id,
                        'section_name' => $section->section_name
                    ])
                    ->values();
            }

            // Courses
            if ($request->has('section_id')) {
                $courseQuery = StudentEnrollment::with('course')
                    ->where('institute_id', $instituteId)
                    ->where('session_id', $request->session_id)
                    ->where('class_id', $request->class_id)
                    ->where('section_id', $request->section_id);

                if ($user->hasRole('Teacher')) {
                    $courseQuery->where('teacher_id', $user->id);
                } elseif ($user->hasRole('Student') || $request->has('student_id')) {
                    $courseQuery->where('student_id', $studentId);
                }

                $data['courses'] = $courseQuery->select('course_id')
                    ->distinct()
                    ->get()
                    ->pluck('course')
                    ->filter()
                    ->map(fn($course) => [
                        'id' => $course->id,
                        'course_name' => $course->course_name
                    ])
                    ->values();
            }

            // Teachers
            if ($request->has('course_id')) {
                $teacherQuery = TeacherEnrollment::with('teacher')
                    ->where('institute_id', $instituteId)
                    ->where('session_id', $request->session_id)
                    ->where('class_id', $request->class_id)
                    ->where('section_id', $request->section_id)
                    ->where('course_id', $request->course_id);

                if ($user->hasRole('Teacher')) {
                    $teacherQuery->where('teacher_id', $user->id);
                } elseif ($user->hasRole('Student') || $request->has('student_id')) {
                    // For students, we need to get teachers for their enrolled courses
                    $teacherQuery->whereHas('studentEnrollments', function ($q) use ($studentId) {
                        $q->where('student_id', $studentId);
                    });
                }

                $data['teachers'] = $teacherQuery->select('teacher_id')
                    ->distinct()
                    ->get()
                    ->pluck('teacher')
                    ->filter()
                    ->map(fn($teacher) => [
                        'id' => $teacher->id,
                        'name' => $teacher->name
                    ])
                    ->values();
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

    public function getTimetable(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'institute_id' => 'required|exists:institutes,id',
                'session_id' => 'required|exists:sessions,id',
                'class_id' => 'required|exists:classes,id',
                'section_id' => 'required|exists:sections,id',
                'course_id' => 'required|exists:courses,id',
                'date' => 'required|date', // Add date validation
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $timetables = TimeTable::where([
                'institute_id' => $request->institute_id,
                'session_id' => $request->session_id,
                'class_id' => $request->class_id,
                'section_id' => $request->section_id,
                'course_id' => $request->course_id,
                'date' => $request->date, // Filter by selected date
            ])->get();

            $formattedTimetables = [];
            foreach ($timetables as $timetable) {
                $timeSlots = explode(',', $timetable->slot_times);
                foreach ($timeSlots as $slot) {
                    $formattedTimetables[] = [
                        'id' => $timetable->id,
                        'time_slot' => trim($slot),
                        'date' => $timetable->date,
                    ];
                }
            }

            return response()->json(['timetables' => $formattedTimetables]);
        } catch (\Exception $e) {
            \Log::error("Error in getTimetable: " . $e->getMessage());
            return response()->json(['error' => 'Failed to load timetable data'], 500);
        }
    }
    public function getStudents(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'institute_id' => 'required|exists:institutes,id',
                'session_id' => 'required|exists:sessions,id',
                'class_id' => 'required|exists:classes,id',
                'section_id' => 'required|exists:sections,id',
                'course_id' => 'required|exists:courses,id',
                'teacher_id' => 'required|exists:users,id',
                'timetable_id' => 'required|exists:timetables,id',
                'date' => 'required|date',
                'slot_times' => 'required|string',
                'is_update' => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            // Set timezone to Asia/Karachi
            date_default_timezone_set('Asia/Karachi');

            // Check if timetable exists and matches the selected date
            $timetable = TimeTable::find($request->timetable_id);

            if (!$timetable) {
                return response()->json(['error' => 'Timetable not found'], 404);
            }

            $selectedDate = Carbon::parse($request->date)->format('Y-m-d');
            $timetableDate = Carbon::parse($timetable->date)->format('Y-m-d');

            if ($timetableDate !== $selectedDate) {
                return response()->json(['error' => 'Selected date does not match the timetable date'], 400);
            }

            // Get all students enrolled in this class/section/course
            $students = StudentEnrollment::with('student')
                ->where([
                    'institute_id' => $request->institute_id,
                    'session_id' => $request->session_id,
                    'class_id' => $request->class_id,
                    'section_id' => $request->section_id,
                    'course_id' => $request->course_id,
                ])
                ->get()
                ->map(function ($enrollment) {
                    return [
                        'enrollment_id' => $enrollment->id,
                        'student_id' => $enrollment->student->id,
                        'name' => $enrollment->student->name,
                        'roll_number' => $enrollment->student->roll_number,
                        'phone' => $enrollment->student->phone,
                        'email' => $enrollment->student->email,
                    ];
                });

            // If this is an update request, get existing attendance status
            if ($request->is_update) {
                $existingAttendance = Attendance::where([
                    'institute_id' => $request->institute_id,
                    'session_id' => $request->session_id,
                    'class_id' => $request->class_id,
                    'section_id' => $request->section_id,
                    'course_id' => $request->course_id,
                    'teacher_id' => $request->teacher_id,
                    'timetable_id' => $request->timetable_id,
                    'date' => $request->date,
                    'slot_times' => $request->slot_times,
                ])->get()->keyBy('student_enrollment_id');

                // Map attendance status to students
                $students = $students->map(function ($student) use ($existingAttendance) {
                    $student['status'] = isset($existingAttendance[$student['enrollment_id']]) 
                        ? $existingAttendance[$student['enrollment_id']]->status 
                        : 1; // Default to present if no record exists
                    return $student;
                });
            }

            return response()->json(['students' => $students, 'is_update' => $request->is_update]);
        } catch (\Exception $e) {
            \Log::error("Error in getStudents: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function markAttendance(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'institute_id' => 'required|exists:institutes,id',
                'session_id' => 'required|exists:sessions,id',
                'class_id' => 'required|exists:classes,id',
                'section_id' => 'required|exists:sections,id',
                'course_id' => 'required|exists:courses,id',
                'teacher_id' => 'required|exists:users,id',
                'timetable_id' => 'required|exists:timetables,id',
                'date' => 'required|date',
                'slot_times' => 'required|string',
                'attendances' => 'required|array',
                'attendances.*.student_enrollment_id' => 'required|exists:student_enrollments,id',
                'attendances.*.student_id' => 'required|exists:users,id',
                'attendances.*.status' => 'required|in:0,1,true,false',
                'is_update' => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            // Set timezone to Asia/Karachi
            date_default_timezone_set('Asia/Karachi');

            // Check if timetable exists and matches the selected date
            $timetable = TimeTable::find($request->timetable_id);

            if (!$timetable) {
                return response()->json(['error' => 'Timetable not found'], 404);
            }

            $selectedDate = Carbon::parse($request->date)->format('Y-m-d');
            $timetableDate = Carbon::parse($timetable->date)->format('Y-m-d');

            if ($timetableDate !== $selectedDate) {
                return response()->json(['error' => 'Selected date does not match the timetable date'], 400);
            }

            // Check if today's date matches the selected date for editing
            $today = Carbon::now()->format('Y-m-d');
            if ($request->boolean('is_update') && $selectedDate !== $today) {
                return response()->json(['error' => 'Attendance can only be edited on the same day'], 403);
            }

            // Get authenticated user ID
            $createdBy = auth()->id();

            // Begin transaction
            DB::beginTransaction();

            try {
                // Check if attendance exists
                $existingAttendance = Attendance::where([
                    'institute_id' => $request->institute_id,
                    'session_id' => $request->session_id,
                    'class_id' => $request->class_id,
                    'section_id' => $request->section_id,
                    'course_id' => $request->course_id,
                    'teacher_id' => $request->teacher_id,
                    'timetable_id' => $request->timetable_id,
                    'date' => $request->date,
                    'slot_times' => $request->slot_times,
                ])->exists();

                // Only check for existing attendance if this is a new entry
                if (!$request->boolean('is_update') && $existingAttendance) {
                    DB::rollBack();
                    return response()->json(['error' => 'Attendance already marked for this class, date and time slot'], 400);
                }

                // Process each attendance record
                foreach ($request->attendances as $attendance) {
                    $attendanceData = [
                        'institute_id' => $request->institute_id,
                        'session_id' => $request->session_id,
                        'class_id' => $request->class_id,
                        'section_id' => $request->section_id,
                        'course_id' => $request->course_id,
                        'teacher_id' => $request->teacher_id,
                        'timetable_id' => $request->timetable_id,
                        'student_enrollment_id' => $attendance['student_enrollment_id'],
                        'student_id' => $attendance['student_id'],
                        'date' => $request->date,
                        'slot_times' => $request->slot_times,
                        'status' => $attendance['status'],
                        'updated_by' => $createdBy,
                        'updated_at' => now(),
                    ];

                    // If it's a new record, add created_by and created_at
                    if (!$request->boolean('is_update')) {
                        $attendanceData['created_by'] = $createdBy;
                        $attendanceData['created_at'] = now();
                    }

                    // Update or create the attendance record
                    Attendance::updateOrCreate(
                        [
                            'institute_id' => $request->institute_id,
                            'session_id' => $request->session_id,
                            'class_id' => $request->class_id,
                            'section_id' => $request->section_id,
                            'course_id' => $request->course_id,
                            'teacher_id' => $request->teacher_id,
                            'timetable_id' => $request->timetable_id,
                            'student_enrollment_id' => $attendance['student_enrollment_id'],
                            'date' => $request->date,
                            'slot_times' => $request->slot_times,
                        ],
                        array_merge($attendanceData, [
                            'attendance_date' => now()->format('Y-m-d')
                        ])
                    );
                }

                DB::commit();

                return response()->json([
                    'message' => $request->boolean('is_update') ? 'Attendance updated successfully' : 'Attendance marked successfully',
                    'status' => 'success'
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            \Log::error("Error in markAttendance: " . $e->getMessage());
            return response()->json(['error' => 'Failed to mark attendance'], 500);
        }
    }



    public function report()
    {
        $institutes = auth()->user()->hasRole('Super Admin')
            ? Institute::all()
            : Institute::where('id', auth()->user()->institute_id)->get();

        return view('attendances.attendanceReport', compact('institutes'));
    }

    public function generateReport(Request $request)
    {
        $request->validate([
            'institute_id' => 'required|exists:institutes,id',
            'session_id' => 'required|exists:sessions,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'course_id' => 'required|exists:courses,id',
            'teacher_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $enrollments = StudentEnrollment::with(['student', 'class', 'section', 'course', 'teacher'])
            ->where([
                'institute_id' => $request->institute_id,
                'session_id' => $request->session_id,
                'class_id' => $request->class_id,
                'section_id' => $request->section_id,
                'course_id' => $request->course_id,
                'teacher_id' => $request->teacher_id,
            ])
            ->get();

        $attendanceData = [];
        foreach ($enrollments as $enrollment) {
            $attendanceRecords = Attendance::where('student_enrollment_id', $enrollment->id)
                ->whereBetween('date', [$request->start_date, $request->end_date])
                ->get();

            $presentCount = $attendanceRecords->where('status', true)->count();
            $totalClasses = $attendanceRecords->count();
            $attendancePercentage = $totalClasses > 0 ? round(($presentCount / $totalClasses) * 100) : 0;

            $attendanceData[] = [
                'enrollment' => $enrollment,
                'student' => $enrollment->student,
                'present_count' => $presentCount,
                'total_classes' => $totalClasses,
                'percentage' => $attendancePercentage,
                'status' => $attendancePercentage >= 75 ? 'Present' : 'Absent'
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'institute' => Institute::find($request->institute_id)->name,
                'session' => $enrollments->first()->session->session_name ?? 'N/A',
                'class' => $enrollments->first()->class->name ?? 'N/A',
                'section' => $enrollments->first()->section->section_name ?? 'N/A',
                'course' => $enrollments->first()->course->course_name ?? 'N/A',
                'teacher' => $enrollments->first()->teacher->name ?? 'N/A',
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'students' => $attendanceData
            ]
        ]);
    }

    public function generatePdf(Request $request)
    {
        $data = $request->validate([
            'institute' => 'required|string',
            'session' => 'required|string',
            'class' => 'required|string',
            'section' => 'required|string',
            'course' => 'required|string',
            'teacher' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'students' => 'required|array'
        ]);

        $pdf = PDF::loadView('attendances.reportPdf', $data);
        return $pdf->download('attendance-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function getCourses(Request $request)
    {
        try {
            $user = auth()->user();
            
            if (!$request->has('session_id')) {
                return response()->json([], 200);
            }

            $instituteId = $user->hasRole('Super Admin') ? $request->institute_id : $user->institute_id;
            $sessionId = $request->session_id;

            if ($user->hasRole('Super Admin') && !$request->has('institute_id')) {
                return response()->json([], 200);
            }

            $query = StudentEnrollment::with(['course', 'class', 'section', 'teacher'])
                ->where('institute_id', $instituteId)
                ->where('session_id', $sessionId);

            if ($user->hasRole('Teacher')) {
                $query->where('teacher_id', $user->id);
            } elseif ($user->hasRole('Student')) {
                $query->where('student_id', $user->id);
            }

            $enrollments = $query->get();

            if ($enrollments->isEmpty()) {
                return response()->json([], 200);
            }

            $courses = $enrollments->map(function ($enrollment) {
                // Check if all relationships are loaded
                if (!$enrollment->course || !$enrollment->class || !$enrollment->section || !$enrollment->teacher) {
                    \Log::error('Missing relationship data for enrollment ID: ' . $enrollment->id);
                    return null;
                }

                return [
                    'id' => $enrollment->course->id,
                    'course_name' => $enrollment->course->course_name,
                    'class_name' => $enrollment->class->name,
                    'section_name' => $enrollment->section->section_name,
                    'teacher_name' => $enrollment->teacher->name,
                    'class_id' => $enrollment->class_id,
                    'section_id' => $enrollment->section_id,
                    'teacher_id' => $enrollment->teacher_id,
                    'background_color' => $enrollment->class->background_color ?? '#3490dc'
                ];
            })->filter()->unique('id')->values();

            return response()->json($courses);
        } catch (\Exception $e) {
            \Log::error('Error in getCourses: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([], 200);
        }
    }

    public function getTimeSlots(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'institute_id' => 'required|exists:institutes,id',
                'session_id' => 'required|exists:sessions,id',
                'class_id' => 'required|exists:classes,id',
                'section_id' => 'required|exists:sections,id',
                'course_id' => 'required|exists:courses,id',
                'teacher_id' => 'required|exists:users,id'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            // Set timezone to Asia/Karachi
            date_default_timezone_set('Asia/Karachi');

            // Get today's date for comparison
            $today = now()->format('Y-m-d');

            $slots = TimeTable::where([
                'institute_id' => $request->institute_id,
                'session_id' => $request->session_id,
                'class_id' => $request->class_id,
                'section_id' => $request->section_id,
                'course_id' => $request->course_id
            ])
            ->orderBy('date', 'desc')
            ->orderBy('slot_times')
            ->get();

            if ($slots->isEmpty()) {
                return response()->json([]);
            }

            // Get existing attendance records
            $attendanceRecords = Attendance::where([
                'institute_id' => $request->institute_id,
                'session_id' => $request->session_id,
                'class_id' => $request->class_id,
                'section_id' => $request->section_id,
                'course_id' => $request->course_id,
                'teacher_id' => $request->teacher_id
            ])
            ->select('date', 'slot_times')
            ->get()
            ->groupBy(function($attendance) {
                return $attendance->date->format('Y-m-d') . '_' . $attendance->slot_times;
            });

            $formattedSlots = [];
            foreach ($slots as $slot) {
                $timeSlots = explode(',', $slot->slot_times);
                foreach ($timeSlots as $timeSlot) {
                    $timeSlot = trim($timeSlot);
                    $slotDate = Carbon::parse($slot->date)->format('Y-m-d');
                    $key = $slotDate . '_' . $timeSlot;
                    $hasAttendance = $attendanceRecords->has($key);

                    $formattedSlots[] = [
                        'id' => $slot->id,
                        'date' => $slotDate,
                        'slot_times' => $timeSlot,
                        'class_id' => $request->class_id,
                        'section_id' => $request->section_id,
                        'course_id' => $request->course_id,
                        'teacher_id' => $request->teacher_id,
                        'status' => $hasAttendance ? 'Uploaded' : 'Available',
                        'can_edit' => $hasAttendance && $slotDate === $today
                    ];
                }
            }

            return response()->json($formattedSlots);
        } catch (\Exception $e) {
            \Log::error('Error in getTimeSlots: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load time slots'], 500);
        }
    }
}
