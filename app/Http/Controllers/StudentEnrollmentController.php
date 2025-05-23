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
use App\Models\Attendance;
use App\Models\ResultUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use League\Csv\Reader;
use Barryvdh\DomPDF\Facade\Pdf;


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

    public function uploadCsv(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'csv_file' => 'required|file|mimes:csv,txt',
            'session_id' => 'required|exists:sessions,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'institute_id' => auth()->user()->hasRole('Super Admin')
                ? 'required|exists:institutes,id'
                : 'nullable'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        try {
            // Get institute ID
            $institute_id = auth()->user()->hasRole('Super Admin')
                ? $request->institute_id
                : auth()->user()->institute_id;

            // Process CSV file
            $file = $request->file('csv_file');
            $csvData = array_map('str_getcsv', file($file->getRealPath()));

            // Remove header if exists
            $header = array_shift($csvData);
            $rollNumbers = array_column($csvData, 0); // Assuming roll_number is first column

            if (empty($rollNumbers)) {
                return response()->json(['error' => 'No roll numbers found in CSV'], 400);
            }

            // Get students from user table
            $students = User::role('Student')
                ->where('institute_id', $institute_id)
                ->whereIn('roll_number', $rollNumbers)
                ->get(['id', 'name', 'roll_number']);

            // Prepare results
            $results = [];
            $notFound = array_diff($rollNumbers, $students->pluck('roll_number')->toArray());

            foreach ($students as $student) {
                // Check enrollment in StudentEnrollment table
                $isEnrolled = StudentEnrollment::where([
                    'student_id' => $student->id,
                    'session_id' => $request->session_id,
                    'class_id' => $request->class_id,
                    'section_id' => $request->section_id
                ])->exists();

                $results[] = [
                    'id' => $student->id,
                    'name' => $student->name,
                    'roll_number' => $student->roll_number,
                    'is_enrolled' => $isEnrolled
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $results,
                'not_found' => array_values($notFound),
                'stats' => [
                    'total' => count($rollNumbers),
                    'found' => count($results),
                    'not_found' => count($notFound),
                    'already_enrolled' => count(array_filter($results, fn($item) => $item['is_enrolled']))
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Processing failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getStudents(Request $request)
    {
        $request->validate([
            'institute_id' => 'required|exists:institutes,id'
        ]);

        try {
            $students = User::role('Student')
                ->where('institute_id', $request->institute_id)
                ->get(['id', 'name', 'roll_number']);

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

           // Get sessions with formatted dates
            $data['sessions'] = Session::where('institute_id', $instituteId)
            ->get(['id', 'session_name', 'start_date', 'end_date'])
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'session_name' => $session->session_name,
                    'start_date' => $session->start_date ? date('Y-m-d', strtotime($session->start_date)) : null,
                    'end_date' => $session->end_date ? date('Y-m-d', strtotime($session->end_date)) : null
                ];
            });

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
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:users,id',
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
            $skipped = [];

            foreach ($request->student_ids as $studentId) {
                foreach ($request->courses as $courseId) {
                    // Check if student is already enrolled in this course for this session/class/section
                    $existing = StudentEnrollment::where([
                        'student_id' => $studentId,
                        'session_id' => $request->session_id,
                        'class_id' => $request->class_id,
                        'section_id' => $request->section_id,
                        'course_id' => $courseId
                    ])->exists();

                    if (!$existing) {
                        $enrollments[] = StudentEnrollment::create([
                            'student_id' => $studentId,
                            'institute_id' => $request->institute_id,
                            'session_id' => $request->session_id,
                            'class_id' => $request->class_id,
                            'section_id' => $request->section_id,
                            'course_id' => $courseId,
                            'enrollment_date' => $request->enrollment_date,
                            'status' => $request->status,
                            'created_by' => Auth::id()
                        ]);
                    } else {
                        $student = User::find($studentId);
                        $course = Course::find($courseId);
                        $skipped[] = "{$student->name} ({$student->roll_number}) - {$course->course_name}";
                    }
                }
            }

            DB::commit();

            $response = [
                'success' => true,
                'message' => 'Students enrolled successfully!',
                'enrollments_count' => count($enrollments)
            ];

            if (!empty($skipped)) {
                $response['skipped'] = $skipped;
                $response['message'] .= ' Some enrollments were skipped (already exists).';
            }

            return response()->json($response);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error enrolling students: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        try {
            // Get the enrollment record
            $enrollment = StudentEnrollment::with(['student', 'institute', 'session', 'class', 'section', 'course'])
                ->findOrFail($id);

            // Get all enrollments for this student/session/class/section
            $enrollments = StudentEnrollment::where([
                'student_id' => $enrollment->student_id,
                'session_id' => $enrollment->session_id,
                'class_id' => $enrollment->class_id,
                'section_id' => $enrollment->section_id
            ])->get();

            // Get all course IDs
            $courseIds = $enrollments->pluck('course_id')->toArray();

            // Get student details
            $student = User::find($enrollment->student_id);

            return response()->json([
                'id' => $id,
                'student_id' => $enrollment->student_id,
                'student_name' => $student->name,
                'student_roll_number' => $student->roll_number,
                'institute_id' => $enrollment->institute_id,
                'session_id' => $enrollment->session_id,
                'class_id' => $enrollment->class_id,
                'section_id' => $enrollment->section_id,
                'enrollment_date' => $enrollment->enrollment_date,
                'status' => $enrollment->status,
                'course_ids' => $courseIds
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load enrollment',
                'message' => $e->getMessage()
            ], 404);
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
            // Get the existing enrollment
            $existingEnrollment = StudentEnrollment::findOrFail($id);

            // Get all existing enrollments for this student/session/class/section
            $existingEnrollments = StudentEnrollment::where([
                'student_id' => $existingEnrollment->student_id,
                'session_id' => $existingEnrollment->session_id,
                'class_id' => $existingEnrollment->class_id,
                'section_id' => $existingEnrollment->section_id
            ])->get();

            // Get existing course IDs
            $existingCourseIds = $existingEnrollments->pluck('course_id')->toArray();
            $newCourseIds = $request->courses;

            // Courses to add (only those that don't exist)
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

            // Courses to remove (only those not in new selection)
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
                'message' => 'Enrollment updated successfully!',
                'added_courses' => count($coursesToAdd),
                'removed_courses' => count($coursesToRemove),
                'unchanged_courses' => count(array_intersect($existingCourseIds, $newCourseIds))
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
            ->addColumn('student_name', function ($enrollment) {
                return $enrollment->student->name ?? 'N/A';
            })
            ->addColumn('institute', function ($enrollment) {
                return $enrollment->institute->name ?? 'N/A';
            })
            ->addColumn('session', function ($enrollment) {
                return optional($enrollment->session)->session_name ?? 'N/A';
            })
            ->addColumn('class', function ($enrollment) {
                return optional($enrollment->class)->name ?? 'N/A';
            })
            ->addColumn('section', function ($enrollment) {
                return optional($enrollment->section)->section_name ?? 'N/A';
            })
            ->addColumn('courses', function ($enrollment) {
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
            ->addColumn('enrollment_date', function ($enrollment) {
                return StudentEnrollment::where([
                    'student_id' => $enrollment->student_id,
                    'session_id' => $enrollment->session_id,
                    'class_id' => $enrollment->class_id,
                    'section_id' => $enrollment->section_id
                ])->value('enrollment_date');
            })
            ->addColumn('status', function ($enrollment) {
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
            ->addColumn('action', function ($enrollment) {
                $id = StudentEnrollment::where([
                    'student_id' => $enrollment->student_id,
                    'session_id' => $enrollment->session_id,
                    'class_id' => $enrollment->class_id,
                    'section_id' => $enrollment->section_id
                ])->value('id');

                return '
                    <div class="btn-group">
                        <button class="btn btn-sm btn-primary edit-btn me-1" data-id="' . $id . '">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="' . $id . '">
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
            $enrollment = StudentEnrollment::findOrFail($id);

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

    // REPORTS

    public function EnrollmentReport()
    {
        $user = auth()->user();
        $institutes = $user->hasRole('Super Admin')
            ? Institute::get()
            : null;

        return view('enrollments.enrollmentreports.showReports', compact('institutes'));
    }

    public function generateReport(Request $request)
    {
        $request->validate([
            'institute_id' => 'required|exists:institutes,id',
            'session_id' => 'required|exists:sessions,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
        ]);

        $enrollments = StudentEnrollment::with(['student', 'course', 'teacher'])
            ->where([
                'institute_id' => $request->institute_id,
                'session_id' => $request->session_id,
                'class_id' => $request->class_id,
                'section_id' => $request->section_id,
            ])
            ->get()
            ->groupBy('student_id');

        $reportData = [];
        $institute = Institute::find($request->institute_id)->name;
        $session = Session::find($request->session_id)->session_name;
        $class = Classes::find($request->class_id)->name;
        $section = Section::find($request->section_id)->section_name;

        foreach ($enrollments as $studentId => $studentEnrollments) {
            $firstEnrollment = $studentEnrollments->first();
            $student = $firstEnrollment->student;

            $courses = [];
            $teachers = [];
            $attendanceData = [];

            foreach ($studentEnrollments as $enrollment) {
                if ($enrollment->course) {
                    $courses[] = $enrollment->course->course_name;

                    // Calculate attendance percentage for each course
                    $attendanceStats = DB::table('attendances')
                        ->select(
                            DB::raw('COUNT(*) as total_days'),
                            DB::raw('SUM(status) as present_days')
                        )
                        ->where([
                            'institute_id' => $request->institute_id,
                            'session_id' => $request->session_id,
                            'class_id' => $request->class_id,
                            'section_id' => $request->section_id,
                            'course_id' => $enrollment->course_id,
                            'student_id' => $studentId,
                        ])
                        ->first();

                    $percentage = 0;
                    if ($attendanceStats->total_days > 0) {
                        $percentage = round(($attendanceStats->present_days / $attendanceStats->total_days) * 100, 2);
                    }

                    $attendanceData[$enrollment->course->course_name] = $percentage;
                }
                if ($enrollment->teacher) {
                    $teachers[] = $enrollment->teacher->name;
                }
            }

            $reportData[] = [
                'id' => $firstEnrollment->id,
                'student' => $student,
                'student_id' => $studentId,
                'courses' => implode(', ', array_unique($courses)),
                'teachers' => implode(', ', array_unique($teachers)),
                'enrollment_date' => $firstEnrollment->enrollment_date,
                'status' => $firstEnrollment->status,
                'attendance' => $attendanceData,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'institute' => $institute,
                'session' => $session,
                'class' => $class,
                'section' => $section,
                'students' => $reportData
            ]
        ]);
    }

    public function studentDetail($studentId)
    {
        // Get the student details
        $student = User::findOrFail($studentId);

        // Get all enrollments for this student with related data
        $enrollments = StudentEnrollment::with([
            'institute',
            'session',
            'class',
            'section',
            'course',
            'teacher'
        ])
            ->where('student_id', $studentId)
            ->orderBy('session_id', 'desc')
            ->get()
            ->groupBy('session_id');

        // Calculate attendance percentages for each course in each session
        $enrollmentsWithAttendance = [];
        foreach ($enrollments as $sessionId => $sessionEnrollments) {
            foreach ($sessionEnrollments as $enrollment) {
                $attendanceStats = DB::table('attendances')
                    ->select(
                        DB::raw('COUNT(*) as total_days'),
                        DB::raw('SUM(status) as present_days')
                    )
                    ->where([
                        'institute_id' => $enrollment->institute_id,
                        'session_id' => $enrollment->session_id,
                        'class_id' => $enrollment->class_id,
                        'section_id' => $enrollment->section_id,
                        'course_id' => $enrollment->course_id,
                        'student_id' => $studentId,
                    ])
                    ->first();

                $percentage = 0;
                if ($attendanceStats->total_days > 0) {
                    $percentage = round(($attendanceStats->present_days / $attendanceStats->total_days) * 100, 2);
                }

                $enrollment->attendance_percentage = $percentage;
            }
            $enrollmentsWithAttendance[$sessionId] = $sessionEnrollments;
        }

        return view('enrollments.enrollmentreports.studentEnrollment', [
            'student' => $student,
            'enrollments' => $enrollmentsWithAttendance
        ]);
    }

    public function generatePdf(Request $request)
    {
        $data = $request->validate([
            'institute' => 'required|string',
            'session' => 'required|string',
            'class' => 'required|string',
            'section' => 'required|string',
            'students' => 'required|array'
        ]);

        $pdf = PDF::loadView('enrollments.enrollmentreports.enrollmentReport', $data);
        return $pdf->download("enrollment-report-{$data['class']}-{$data['section']}-" . now()->format('Y-m-d') . '.pdf');
    }

    public function generateStudentPdf(Request $request)
    {
        $data = $request->validate([
            'student' => 'required|array',
            'enrollments' => 'required|array',
        ]);

        // Convert profile image to base64 for PDF
        if (isset($data['student']['profile_image']) && $data['student']['profile_image']) {
            $imagePath = storage_path('app/public/' . $data['student']['profile_image']);
            if (file_exists($imagePath)) {
                $imageData = base64_encode(file_get_contents($imagePath));
                $data['student']['profile_image_base64'] = 'data:image/' . pathinfo($imagePath, PATHINFO_EXTENSION) . ';base64,' . $imageData;
            }
        }

        // Get default image as fallback
        $defaultImagePath = public_path('images/1.png');
        $defaultImageData = base64_encode(file_get_contents($defaultImagePath));
        $data['default_profile_image'] = 'data:image/png;base64,' . $defaultImageData;

        // Get institute name
        $firstEnrollment = collect($data['enrollments'])->first();
        $data['name'] = $firstEnrollment['institute']['name'] ?? 'Institute';

        $pdf = PDF::loadView('enrollments.enrollmentreports.studentpdf', $data);
        return $pdf->download("student-{$data['student']['roll_number']}-history.pdf");
    }

    public function attendanceDetails(Request $request)
    {
        try {
            $request->validate([
                'enrollment_id' => 'required|exists:student_enrollments,id',
                'student_id' => 'required|exists:users,id',
                'course_id' => 'required|exists:courses,id'
            ]);

            $enrollment = StudentEnrollment::with(['session', 'class', 'section', 'course', 'student'])
                ->findOrFail($request->enrollment_id);

            $attendanceRecords = Attendance::where([
                'institute_id' => $enrollment->institute_id,
                'session_id' => $enrollment->session_id,
                'class_id' => $enrollment->class_id,
                'section_id' => $enrollment->section_id,
                'course_id' => $request->course_id,
                'student_id' => $request->student_id,
            ])
                ->orderBy('date', 'asc')
                ->get();

            $totalDays = $attendanceRecords->count();
            $presentDays = $attendanceRecords->where('status', 1)->count();
            $percentage = $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 2) : 0;

            $formattedAttendance = $attendanceRecords->map(function ($record) {
                // Convert to Pakistan timezone and format date
                $date = \Carbon\Carbon::parse($record->date)
                    ->timezone('Asia/Karachi')
                    ->format('d-m-Y');

                $timeSlot = $record->slot_times ?: 'N/A';

                return [
                    'date' => $date,
                    'slot_times' => $timeSlot,
                    'status' => $record->status ? 'Present' : 'Absent'
                ];
            });

            return response()->json([
                'student' => $enrollment->student,
                'course' => $enrollment->course,
                'session' => $enrollment->session,
                'class' => $enrollment->class,
                'section' => $enrollment->section,
                'attendance' => $formattedAttendance,
                'percentage' => $percentage,
                'total_days' => $totalDays,
                'present_days' => $presentDays
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load attendance details',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function generateAttendancePdf(Request $request)
    {
        try {
            $data = $request->validate([
                'student' => 'required|array',
                'course' => 'required|array',
                'session' => 'required|array',
                'class' => 'required|array',
                'section' => 'required|array',
                'attendance' => 'required|array',
                'percentage' => 'required|numeric',
                'total_days' => 'required|integer',
                'present_days' => 'required|integer'
            ]);

            $pdf = PDF::loadView('enrollments.enrollmentreports.attendancePdf', $data);
            return $pdf->download("attendance-{$data['student']['id']}-{$data['course']['id']}.pdf");
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate PDF',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function resultDetails(Request $request)
    {
        try {
            $request->validate([
                'enrollment_id' => 'required|exists:student_enrollments,id',
                'student_id' => 'required|exists:users,id',
                'course_id' => 'required|exists:courses,id'
            ]);

            $enrollment = StudentEnrollment::with(['session', 'class', 'section', 'course', 'student'])
                ->findOrFail($request->enrollment_id);

            $result = ResultUpload::where([
                'institute_id' => $enrollment->institute_id,
                'session_id' => $enrollment->session_id,
                'class_id' => $enrollment->class_id,
                'section_id' => $enrollment->section_id,
                'course_id' => $request->course_id,
                'student_id' => $request->student_id,
            ])->first();

            if (!$result) {
                return response()->json([
                    'error' => 'No result found for this student and course'
                ], 404);
            }

            $course = Course::with(['assessments' => function ($query) {
                $query->orderByRaw("
                    CASE 
                        WHEN type LIKE 'assignment%' THEN 1
                        WHEN type LIKE 'quiz%' THEN 2
                        WHEN type LIKE 'midterm%' THEN 3
                        WHEN type LIKE 'final%' THEN 4
                        ELSE 5
                    END
                ")->orderBy('title');
            }])->find($request->course_id);

            $assessments = [];
            $totalWeightage = 0;

            // Process assignments
            for ($i = 1; $i <= 4; $i++) {
                $field = 'assignment' . $i;
                if ($result->$field !== null) {
                    $assessment = $this->findAssessment($course->assessments, 'assignment', $i);
                    $assessments[] = [
                        'name' => $assessment ? $assessment->title : 'Assignment ' . $i,
                        'obtained' => $result->$field,
                        'total' => $assessment ? $assessment->marks : 0,
                        'weightage' => $assessment ? $assessment->weightage_percent : 0,
                        'remarks' => $this->getRemarks($result->$field, $assessment ? $assessment->marks : 0)
                    ];
                    $totalWeightage += $assessment ? $assessment->weightage_percent : 0;
                }
            }

            // Process quizzes
            for ($i = 1; $i <= 3; $i++) {
                $field = 'quiz' . $i;
                if ($result->$field !== null) {
                    $assessment = $this->findAssessment($course->assessments, 'quiz', $i);
                    $assessments[] = [
                        'name' => $assessment ? $assessment->title : 'Quiz ' . $i,
                        'obtained' => $result->$field,
                        'total' => $assessment ? $assessment->marks : 0,
                        'weightage' => $assessment ? $assessment->weightage_percent : 0,
                        'remarks' => $this->getRemarks($result->$field, $assessment ? $assessment->marks : 0)
                    ];
                    $totalWeightage += $assessment ? $assessment->weightage_percent : 0;
                }
            }

            // Process midterm and final
            if ($result->midterm !== null) {
                $assessment = $this->findAssessment($course->assessments, 'midterm');
                $assessments[] = [
                    'name' => $assessment ? $assessment->title : 'Mid Term',
                    'obtained' => $result->midterm,
                    'total' => $assessment ? $assessment->marks : 0,
                    'weightage' => $assessment ? $assessment->weightage_percent : 0,
                    'remarks' => $this->getRemarks($result->midterm, $assessment ? $assessment->marks : 0)
                ];
                $totalWeightage += $assessment ? $assessment->weightage_percent : 0;
            }

            if ($result->final !== null) {
                $assessment = $this->findAssessment($course->assessments, 'final');
                $assessments[] = [
                    'name' => $assessment ? $assessment->title : 'Final Term',
                    'obtained' => $result->final,
                    'total' => $assessment ? $assessment->marks : 0,
                    'weightage' => $assessment ? $assessment->weightage_percent : 0,
                    'remarks' => $this->getRemarks($result->final, $assessment ? $assessment->marks : 0)
                ];
                $totalWeightage += $assessment ? $assessment->weightage_percent : 0;
            }

            $percentage = ($result->obtained_marks / $result->total_marks) * 100;
            $grade = $this->calculateGrade($percentage);

            return response()->json([
                'student' => $enrollment->student,
                'course' => $enrollment->course,
                'session' => $enrollment->session,
                'class' => $enrollment->class,
                'section' => $enrollment->section,
                'result' => [
                    'obtained_marks' => $result->obtained_marks,
                    'total_marks' => $result->total_marks,
                    'status' => $percentage >= 40 ? 'Pass' : 'Fail'
                ],
                'assessments' => $assessments,
                'percentage' => number_format($percentage, 2),
                'grade' => $grade,
                'total_weightage' => $totalWeightage
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load result details',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function generateResultPdf(Request $request)
    {
        try {
            $data = $request->validate([
                'student' => 'required|array',
                'course' => 'required|array',
                'session' => 'required|array',
                'class' => 'required|array',
                'section' => 'required|array',
                'result' => 'required|array',
                'assessments' => 'required|array',
                'percentage' => 'required|numeric',
                'grade' => 'required|string'
            ]);

            $pdf = PDF::loadView('enrollments.enrollmentreports.resultPdf', $data);
            return $pdf->download("result-{$data['student']['id']}-{$data['course']['id']}.pdf");
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate PDF',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    // Helper methods
    private function findAssessment($assessments, $type, $number = null)
    {
        foreach ($assessments as $assessment) {
            $assessmentType = strtolower($assessment->type);
            if (str_contains($assessmentType, $type)) {
                if ($number) {
                    $assessmentNumber = preg_replace('/[^0-9]/', '', $assessment->title);
                    if ($assessmentNumber == $number) {
                        return $assessment;
                    }
                } else {
                    return $assessment;
                }
            }
        }
        return null;
    }

    private function getRemarks($obtained, $total)
    {
        if ($total == 0) return 'N/A';

        $percentage = ($obtained / $total * 100);
        if ($percentage >= 90) return 'Outstanding';
        if ($percentage >= 80) return 'Excellent';
        if ($percentage >= 70) return 'Very Good';
        if ($percentage >= 60) return 'Good';
        if ($percentage >= 50) return 'Satisfactory';
        if ($percentage >= 40) return 'Needs Improvement';
        return 'Poor';
    }

    private function calculateGrade($percentage)
    {
        if ($percentage >= 90) return 'A+';
        if ($percentage >= 85) return 'A';
        if ($percentage >= 80) return 'A-';
        if ($percentage >= 75) return 'B+';
        if ($percentage >= 70) return 'B';
        if ($percentage >= 65) return 'B-';
        if ($percentage >= 60) return 'C+';
        if ($percentage >= 55) return 'C';
        if ($percentage >= 50) return 'C-';
        if ($percentage >= 45) return 'D';
        return 'F';
    }
}
