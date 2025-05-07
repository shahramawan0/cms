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
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;

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
            'course_id' => 'required|exists:courses,id',
            'enrollment_date' => 'required|date',
            'status' => 'required|in:active,inactive,archived',
        ]);

        try {
            $data = $request->all();
            $data['created_by'] = Auth::id();
            
            $enrollment = StudentEnrollment::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Student enrolled successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error enrolling student: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        try {
            $enrollment = StudentEnrollment::with(['student', 'institute', 'session', 'class', 'section', 'course'])
                ->findOrFail($id);
                
            return response()->json([
                'id' => $enrollment->id,
                'student_id' => $enrollment->student_id,
                'institute_id' => $enrollment->institute_id,
                'session_id' => $enrollment->session_id,
                'class_id' => $enrollment->class_id,
                'section_id' => $enrollment->section_id,
                'course_id' => $enrollment->course_id,
                'enrollment_date' => $enrollment->enrollment_date,
                'status' => $enrollment->status
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
            'course_id' => 'required|exists:courses,id',
            'enrollment_date' => 'required|date',
            'status' => 'required|in:active,inactive,archived',
        ]);

        try {
            $enrollment = StudentEnrollment::findOrFail($id);
            $data = $request->all();
            $data['updated_by'] = Auth::id();
            
            $enrollment->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Enrollment updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating enrollment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getEnrollments()
    {
        $user = auth()->user();

        $query = StudentEnrollment::with(['student', 'institute', 'session', 'class', 'section', 'course']);

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
            ->addColumn('course', function($enrollment) {
                return optional($enrollment->course)->course_name ?? 'N/A';
            })
            ->addColumn('enrollment_date', function($enrollment) {
                return $enrollment->enrollment_date;
            })
            ->addColumn('status', function($enrollment) {
                return $enrollment->status === 'active'
                    ? '<span class="badge bg-success">Active</span>'
                    : ($enrollment->status === 'inactive' 
                        ? '<span class="badge bg-warning">Inactive</span>'
                        : '<span class="badge bg-secondary">Archived</span>');
            })
            ->addColumn('action', function($enrollment) {
                return '
                    <div class="btn-group">
                        <button class="btn btn-sm btn-primary edit-btn me-1" data-id="'.$enrollment->id.'">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="'.$enrollment->id.'">
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
        try {
            $enrollment = StudentEnrollment::findOrFail($id);
            $enrollment->updated_by = Auth::id();
            $enrollment->save();
            $enrollment->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Enrollment deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting enrollment: ' . $e->getMessage()
            ], 500);
        }
    }
}