<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Institute;
use App\Models\CourseAssessment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

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
        $courses = Course::with(['institute', 'assessments']);

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
            ->addColumn('status', function($course) {
                return $course->is_active 
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>';
            })
            ->addColumn('action', function($course) {
                $assessmentBtnText = $course->assessments->isEmpty() 
                    ? '<i class="fas fa-plus"></i> Add Assessments'
                    : '<i class="fas fa-edit"></i> Edit Assessments';
                
                $assessmentBtnClass = $course->assessments->isEmpty()
                    ? 'btn-info'
                    : 'btn-warning';
                
                return '
                    <div class="btn-group">
                        <button class="btn btn-sm btn-primary edit-btn me-1" data-id="'.$course->id.'">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-sm '.$assessmentBtnClass.' assessment-btn me-1" data-id="'.$course->id.'">
                            '.$assessmentBtnText.'
                        </button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="'.$course->id.'">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['institute', 'duration', 'status', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'institute_id' => 'required|exists:institutes,id',
            'course_name' => 'required|string|max:255',
            'course_code' => 'nullable|string|max:50|unique:courses,course_code',
            'description' => 'nullable|string',
            'duration_months' => 'nullable|integer|min:1',
            'total_marks' => 'nullable|integer|min:0',
            'credit_hours' => 'nullable|integer|min:0',
            'is_active' => 'required|boolean',
        ]);

        try {
            $data = $request->all();
            $data['created_by'] = Auth::id();
            
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
            'duration_months' => 'nullable|integer|min:1',
            'total_marks' => 'nullable|integer|min:0',
            'credit_hours' => 'nullable|integer|min:0',
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

    public function getAssessments($courseId)
    {
        $assessments = CourseAssessment::where('course_id', $courseId)->get();
        return response()->json($assessments);
    }

    public function storeAssessment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'assessments' => 'required|array|min:1',
            'assessments.*.type' => 'required|in:Assignment,Quiz,Midterm,Final',
            'assessments.*.title' => 'required|string|max:255',
            'assessments.*.marks' => 'required|integer|min:1',
            'assessments.*.weightage_percent' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $course = Course::findOrFail($request->course_id);
            $assessments = $request->assessments;

            // Validate total weightage
            $totalWeightage = collect($assessments)->sum('weightage_percent');
            if (abs($totalWeightage - 100) > 0.01) { // Allow for floating point precision
                return response()->json([
                    'success' => false,
                    'message' => 'Total weightage must equal 100% (Current: '.$totalWeightage.'%)'
                ], 422);
            }

            // Validate total marks don't exceed course total marks
            $totalMarks = collect($assessments)->sum('marks');
            if ($course->total_marks && $totalMarks > $course->total_marks) {
                return response()->json([
                    'success' => false,
                    'message' => 'Total marks of assessments ('.$totalMarks.') exceed course total marks ('.$course->total_marks.')'
                ], 422);
            }

            // Delete existing assessments
            CourseAssessment::where('course_id', $course->id)->delete();

            // Create new assessments
            foreach ($assessments as $assessment) {
                CourseAssessment::create([
                    'course_id' => $course->id,
                    'type' => $assessment['type'],
                    'title' => $assessment['title'],
                    'marks' => $assessment['marks'],
                    'weightage_percent' => $assessment['weightage_percent'],
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Assessments saved successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving assessments: ' . $e->getMessage()
            ], 500);
        }
    }
}