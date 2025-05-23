<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ResultUpload;
use App\Models\User;
use App\Models\Institute;
use App\Models\Session;
use App\Models\Classes;
use App\Models\Section;
use App\Models\Course;
use App\Models\CourseAssessment;
use App\Models\StudentEnrollment;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;




class ResultUploadController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $institutes = $user->hasRole('Super Admin') ? Institute::get() : null;

        return view('results.index', compact('institutes'));
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

            // Sessions
            $sessionQuery = StudentEnrollment::with('session')
                ->where('institute_id', $instituteId);

            if ($user->hasRole('Teacher')) {
                $sessionQuery->where('teacher_id', $user->id);
            }

            $data['sessions'] = $sessionQuery->select('session_id')
                ->distinct()
                ->get()
                ->pluck('session')
                ->filter()
                ->map(fn($session) => [
                    'id' => $session->id,
                    'session_name' => $session->session_name
                ])
                ->values();

            // Classes
            if ($request->has('session_id')) {
                $classQuery = StudentEnrollment::with('class')
                    ->where('institute_id', $instituteId)
                    ->where('session_id', $request->session_id);

                if ($user->hasRole('Teacher')) {
                    $classQuery->where('teacher_id', $user->id);
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
                }

                $data['courses'] = $courseQuery->select('course_id')
                    ->distinct()
                    ->get()
                    ->pluck('course')
                    ->filter()
                    ->map(fn($course) => [
                        'id' => $course->id,
                        'course_name' => $course->course_name,
                        'total_marks' => $course->total_marks,
                        'credit_hours' => $course->credit_hours
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

    // ResultController.php

    public function getStudents(Request $request)
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

            // Get course with assessments ordered by type and title
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

            if (!$course) {
                return response()->json(['error' => 'Course not found'], 404);
            }

            // Check if results already exist for this combination
            $existingResults = ResultUpload::where([
                'institute_id' => $request->institute_id,
                'session_id' => $request->session_id,
                'class_id' => $request->class_id,
                'section_id' => $request->section_id,
                'course_id' => $request->course_id,
                'teacher_id' => $request->teacher_id
            ])->exists();

            // Get all students enrolled in this class/section/course
            $students = StudentEnrollment::with(['student', 'session', 'class', 'section'])
                ->where([
                    'institute_id' => $request->institute_id,
                    'session_id' => $request->session_id,
                    'class_id' => $request->class_id,
                    'section_id' => $request->section_id,
                    'course_id' => $request->course_id,
                ])
                ->get()
                ->map(function ($enrollment) use ($course, $request) {
                    // Get existing result if any
                    $result = ResultUpload::where([
                        'session_id' => $request->session_id,
                        'class_id' => $request->class_id,
                        'section_id' => $request->section_id,
                        'course_id' => $request->course_id,
                        'student_id' => $enrollment->student_id,
                        'student_enrollment_id' => $enrollment->id
                    ])->first();

                    // Create a base student data array
                    $studentData = [
                        'enrollment_id' => $enrollment->id,
                        'student_id' => $enrollment->student->id,
                        'name' => $enrollment->student->name,
                        'session_name' => $enrollment->session->session_name,
                        'class_name' => $enrollment->class->name,
                        'section_name' => $enrollment->section->section_name,
                        'course_name' => $course->course_name,
                        'total_marks' => $course->total_marks,
                        'obtained_marks' => $result ? $result->obtained_marks : null,
                        'has_existing_result' => $result ? true : false
                    ];

                    // Dynamically map assessment values based on the course assessments
                    if ($course->assessments) {
                        foreach ($course->assessments as $assessment) {
                            $type = strtolower($assessment->type);
                            $fieldName = '';

                            // Determine the field name based on assessment type and title
                            if (str_contains($type, 'assignment')) {
                                $number = preg_replace('/[^0-9]/', '', $assessment->title);
                                $fieldName = 'assignment' . ($number ?: '');
                            } elseif (str_contains($type, 'quiz')) {
                                $number = preg_replace('/[^0-9]/', '', $assessment->title);
                                $fieldName = 'quiz' . ($number ?: '');
                            } elseif (str_contains($type, 'midterm')) {
                                $fieldName = 'midterm';
                            } elseif (str_contains($type, 'final')) {
                                $fieldName = 'final';
                            }

                            if ($fieldName && $result) {
                                $studentData[$fieldName] = $result->{$fieldName} ?? null;
                            } else if ($fieldName) {
                                $studentData[$fieldName] = null;
                            }
                        }
                    }

                    return $studentData;
                });

            return response()->json([
                'students' => $students,
                'course' => $course,
                'assessments' => $course->assessments,
                'has_existing_results' => $existingResults
            ]);
        } catch (\Exception $e) {
            \Log::error("Error in getStudents: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'institute_id' => 'required|exists:institutes,id',
                'session_id' => 'required|exists:sessions,id',
                'class_id' => 'required|exists:classes,id',
                'section_id' => 'required|exists:sections,id',
                'course_id' => 'required|exists:courses,id',
                'teacher_id' => 'required|exists:users,id',
                'results' => 'required|array',
                'results.*.student_enrollment_id' => 'required|exists:student_enrollments,id',
                'results.*.student_id' => 'required|exists:users,id',
                'results.*.obtained_marks' => 'required|numeric|min:0',
                'results.*.total_marks' => 'required|numeric|min:0',
                'results.*.course_total' => 'required|numeric|min:0',
                'results.*.assignment1' => 'nullable|numeric|min:0',
                'results.*.assignment2' => 'nullable|numeric|min:0',
                'results.*.assignment3' => 'nullable|numeric|min:0',
                'results.*.assignment4' => 'nullable|numeric|min:0',
                'results.*.quiz1' => 'nullable|numeric|min:0',
                'results.*.quiz2' => 'nullable|numeric|min:0',
                'results.*.quiz3' => 'nullable|numeric|min:0',
                'results.*.midterm' => 'nullable|numeric|min:0',
                'results.*.final' => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            // Get course details
            $course = Course::find($request->course_id);
            if (!$course) {
                return response()->json(['error' => 'Course not found'], 404);
            }

            $createdBy = auth()->id();
            $hasExistingResults = false;

            foreach ($request->results as $resultData) {
                // Validate obtained marks don't exceed course total
                if ($resultData['obtained_marks'] > $resultData['course_total']) {
                    DB::rollBack();
                    return response()->json([
                        'error' => "Obtained marks cannot exceed course total for student ID: {$resultData['student_id']}"
                    ], 400);
                }

                // Check if results already exist for this student/course combination
                $existingResult = ResultUpload::where([
                    'session_id' => $request->session_id,
                    'class_id' => $request->class_id,
                    'section_id' => $request->section_id,
                    'course_id' => $request->course_id,
                    'student_id' => $resultData['student_id'],
                    'student_enrollment_id' => $resultData['student_enrollment_id']
                ])->first();

                if ($existingResult) {
                    $hasExistingResults = true;
                }

                // Process assessment fields - convert empty strings or null to 0
                $assessmentFields = [
                    'assignment1',
                    'assignment2',
                    'assignment3',
                    'assignment4',
                    'quiz1',
                    'quiz2',
                    'quiz3',
                    'midterm',
                    'final'
                ];

                $processedData = [];
                foreach ($assessmentFields as $field) {
                    // Convert empty strings to 0, but keep actual 0 values
                    $processedData[$field] = isset($resultData[$field]) && $resultData[$field] !== ''
                        ? $resultData[$field]
                        : 0;
                }

                // Create or update result with all marks in single table
                $result = ResultUpload::updateOrCreate(
                    [
                        'session_id' => $request->session_id,
                        'class_id' => $request->class_id,
                        'section_id' => $request->section_id,
                        'course_id' => $request->course_id,
                        'student_id' => $resultData['student_id'],
                        'student_enrollment_id' => $resultData['student_enrollment_id']
                    ],
                    [
                        'institute_id' => $request->institute_id,
                        'teacher_id' => $request->teacher_id,
                        'assignment1' => $processedData['assignment1'],
                        'assignment2' => $processedData['assignment2'],
                        'assignment3' => $processedData['assignment3'],
                        'assignment4' => $processedData['assignment4'],
                        'quiz1' => $processedData['quiz1'],
                        'quiz2' => $processedData['quiz2'],
                        'quiz3' => $processedData['quiz3'],
                        'midterm' => $processedData['midterm'],
                        'final' => $processedData['final'],
                        'obtained_marks' => $resultData['obtained_marks'],
                        'total_marks' => $resultData['total_marks'],
                        'course_total' => $resultData['course_total'],
                        'credit_hours' => $course->credit_hours,
                        'updated_by' => $createdBy,
                        'updated_at' => now(),
                        'created_by' => $existingResult ? $existingResult->created_by : $createdBy,
                        'created_at' => $existingResult ? $existingResult->created_at : now()
                    ]
                );
            }

            DB::commit();
            return response()->json([
                'message' => $hasExistingResults ? 'Results updated successfully' : 'Results saved successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error in store: " . $e->getMessage());
            return response()->json(['error' => 'Failed to save results: ' . $e->getMessage()], 500);
        }
    }

    public function view()
    {
        $user = auth()->user();
        $institutes = $user->hasRole('Super Admin') ? Institute::get() : null;

        return view('results.viewResult', compact('institutes'));
    }

    /**
     * Get results data for viewing
     */
    public function getViewData(Request $request)
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

            // Get basic information
            $session = Session::find($request->session_id);
            $class = Classes::find($request->class_id);
            $section = Section::find($request->section_id);
            $course = Course::find($request->course_id);
            $teacher = User::find($request->teacher_id);

            // Get all results for this combination
            $results = ResultUpload::with(['student', 'enrollment'])
                ->where([
                    'institute_id' => $request->institute_id,
                    'session_id' => $request->session_id,
                    'class_id' => $request->class_id,
                    'section_id' => $request->section_id,
                    'course_id' => $request->course_id,
                    'teacher_id' => $request->teacher_id
                ])
                ->get()
                ->map(function ($result) {
                    $percentage = ($result->obtained_marks / $result->total_marks) * 100;
                    return [
                        'id' => $result->id,
                        'student_id' => $result->student_id,
                        'student_name' => $result->student->name,
                        'roll_number' => $result->student->roll_number,
                        'total_marks' => $result->total_marks,
                        'obtained_marks' => $result->obtained_marks,
                        'percentage' => number_format($percentage, 2),
                        'grade' => $this->calculateGrade($percentage),
                        'status' => $percentage >= 40 ? 'Pass' : 'Fail',
                        'updated_at' => $result->updated_at->format('d-m-Y')
                    ];
                });

            return response()->json([
                'results' => $results,
                'session_name' => $session->session_name,
                'class_name' => $class->name,
                'section_name' => $section->section_name,
                'course_name' => $course->course_name,
                'teacher_name' => $teacher->name
            ]);

        } catch (\Exception $e) {
            \Log::error("Error in getViewData: " . $e->getMessage());
            return response()->json(['error' => 'Failed to load results'], 500);
        }
    }

    /**
     * Get detailed result for modal
     */
    public function getResultDetails(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'result_id' => 'required|exists:result_uploads,id'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            // Get the result with all related data
            $result = ResultUpload::with(['student', 'session', 'class', 'section', 'course', 'teacher'])
                ->find($request->result_id);

            if (!$result) {
                return response()->json(['error' => 'Result not found'], 404);
            }

            // Get course assessments
            $course = Course::with(['assessments' => function($query) {
                $query->orderByRaw("
                    CASE 
                        WHEN type LIKE 'assignment%' THEN 1
                        WHEN type LIKE 'quiz%' THEN 2
                        WHEN type LIKE 'midterm%' THEN 3
                        WHEN type LIKE 'final%' THEN 4
                        ELSE 5
                    END
                ")->orderBy('title');
            }])->find($result->course_id);

            // Prepare assessment data
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
                'result' => [
                    'id' => $result->id,
                    'student_name' => $result->student->name,
                    'roll_number' => $result->student->roll_number,
                    'session_name' => $result->session->session_name,
                    'class_name' => $result->class->name,
                    'section_name' => $result->section->section_name,
                    'course_name' => $result->course->course_name,
                    'teacher_name' => $result->teacher->name,
                    'total_marks' => $result->total_marks,
                    'obtained_marks' => $result->obtained_marks,
                    'status' => $percentage >= 40 ? 'Pass' : 'Fail',
                    'updated_at' => $result->updated_at->format('d-m-Y')
                ],
                'assessments' => $assessments,
                'total_weightage' => $totalWeightage,
                'percentage' => number_format($percentage, 2),
                'grade' => $grade
            ]);

        } catch (\Exception $e) {
            \Log::error("Error in getResultDetails: " . $e->getMessage());
            return response()->json(['error' => 'Failed to load result details'], 500);
        }
    }

    /**
     * Generate PDF for all results
     */
    public function generatePdf(Request $request)
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
                return redirect()->back()->with('error', $validator->errors()->first());
            }

            // Get all necessary data
            $session = Session::find($request->session_id);
            $class = Classes::find($request->class_id);
            $section = Section::find($request->section_id);
            $course = Course::find($request->course_id);
            $teacher = User::find($request->teacher_id);
            $institute = Institute::find($request->institute_id);

            $results = ResultUpload::with(['student'])
                ->where([
                    'institute_id' => $request->institute_id,
                    'session_id' => $request->session_id,
                    'class_id' => $request->class_id,
                    'section_id' => $request->section_id,
                    'course_id' => $request->course_id,
                    'teacher_id' => $request->teacher_id
                ])
                ->get()
                ->map(function ($result) {
                    $percentage = ($result->obtained_marks / $result->total_marks) * 100;
                    return [
                        'name' => $result->student->name,
                        'roll_number' => $result->student->roll_number,
                        'total_marks' => $result->total_marks,
                        'obtained_marks' => $result->obtained_marks,
                        'percentage' => number_format($percentage, 2),
                        'grade' => $this->calculateGrade($percentage),
                        'status' => $percentage >= 40 ? 'Pass' : 'Fail'
                    ];
                });

            $data = [
                'institute_name' => $institute->name,
                'session_name' => $session->session_name,
                'class_name' => $class->name,
                'section_name' => $section->section_name,
                'course_name' => $course->course_name,
                'teacher_name' => $teacher->name,
                'results' => $results,
                'date' => now()->format('d-m-Y')
            ];

            $pdf = PDF::loadView('results.viewResultPdf', $data);
            return $pdf->stream('results.pdf');

        } catch (\Exception $e) {
            \Log::error("Error generating PDF: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate PDF');
        }
    }

    /**
     * Generate PDF for single student result
     */
    public function generateStudentPdf(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'result_id' => 'required|exists:result_uploads,id'
            ]);

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->errors()->first());
            }

            // Get the result with all related data
            $result = ResultUpload::with(['student', 'session', 'class', 'section', 'course', 'teacher', 'institute'])
                ->find($request->result_id);

            if (!$result) {
                return redirect()->back()->with('error', 'Result not found');
            }

            // Get course assessments
            $course = Course::with(['assessments' => function($query) {
                $query->orderByRaw("
                    CASE 
                        WHEN type LIKE 'assignment%' THEN 1
                        WHEN type LIKE 'quiz%' THEN 2
                        WHEN type LIKE 'midterm%' THEN 3
                        WHEN type LIKE 'final%' THEN 4
                        ELSE 5
                    END
                ")->orderBy('title');
            }])->find($result->course_id);

            // Prepare assessment data
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

            $data = [
                'institute_name' => $result->institute->name,
                'session_name' => $result->session->session_name,
                'class_name' => $result->class->name,
                'section_name' => $result->section->section_name,
                'course_name' => $result->course->course_name,
                'teacher_name' => $result->teacher->name,
                'student_name' => $result->student->name,
                'roll_number' => $result->student->roll_number,
                'total_marks' => $result->total_marks,
                'obtained_marks' => $result->obtained_marks,
                'percentage' => number_format($percentage, 2),
                'grade' => $grade,
                'status' => $percentage >= 40 ? 'Pass' : 'Fail',
                'assessments' => $assessments,
                'total_weightage' => $totalWeightage,
                'date' => $result->updated_at->format('d-m-Y')
            ];

            $pdf = PDF::loadView('results.studentPdf', $data);
            return $pdf->stream('student-result.pdf');

        } catch (\Exception $e) {
            \Log::error("Error generating student PDF: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate PDF');
        }
    }

    // Helper methods
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

    
}
