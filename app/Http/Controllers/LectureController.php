<?php

namespace App\Http\Controllers;

use App\Models\Lecture;
use App\Models\Institute;
use App\Models\StudentEnrollment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LectureController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $institutes = $user->hasRole('Super Admin') ? Institute::get() : null;

        return view('lectures.index', compact('institutes'));
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
                $teacherQuery->whereHas('studentEnrollments', function($q) use ($studentId) {
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

    public function getTeachers(Request $request)
    {
        try {
            $request->validate([
                'course_id' => 'required|exists:courses,id',
                'institute_id' => 'required|exists:institutes,id',
                'session_id' => 'required|exists:sessions,id',
                'class_id' => 'required|exists:classes,id',
                'section_id' => 'required|exists:sections,id'
            ]);

            $teachers = StudentEnrollment::with('teacher') // relation name
                ->where('institute_id', $request->institute_id)
                ->where('session_id', $request->session_id)
                ->where('class_id', $request->class_id)
                ->where('section_id', $request->section_id)
                ->where('course_id', $request->course_id)
                ->whereHas('teacher', function ($q) {
                    $q->role('Teacher'); // Spatie role check
                })
                ->get()
                ->pluck('teacher') // pull User model
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name
                    ];
                })
                ->unique('id')
                ->values();

            return response()->json($teachers);
        } catch (\Exception $e) {
            \Log::error("Error in getTeachers: " . $e->getMessage());
            return response()->json([
                'error' => 'Failed to load teachers',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'teacher_id' => 'required|exists:users,id',
            'institute_id' => 'required|exists:institutes,id',
            'session_id' => 'required|exists:sessions,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'course_id' => 'required|exists:courses,id',
            'lecture_date' => 'required|date',
            'status' => 'required|in:active,inactive',
            'video' => 'required_without:pdf|file|mimes:mp4|max:51200',
            'pdf' => 'required_without:video|file|mimes:pdf|max:10240'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Verify the teacher is actually enrolled in this course/section
            $isTeacherEnrolled = StudentEnrollment::where([
                'teacher_id' => $request->teacher_id,
                'institute_id' => $request->institute_id,
                'session_id' => $request->session_id,
                'class_id' => $request->class_id,
                'section_id' => $request->section_id,
                'course_id' => $request->course_id
            ])->exists();

            if (!$isTeacherEnrolled) {
                throw new \Exception('The selected teacher is not enrolled in this course/section');
            }

            $lecture = new Lecture();
            $lecture->fill($request->only([
                'title',
                'description',
                'teacher_id',
                'institute_id',
                'session_id',
                'class_id',
                'section_id',
                'course_id',
                'lecture_date',
                'status'
            ]));

            // Handle file uploads
            if ($request->hasFile('video')) {
                $videoPath = $request->file('video')->store('lectures/videos', 'public');
                $lecture->video_path = $videoPath;
            }

            if ($request->hasFile('pdf')) {
                $pdfPath = $request->file('pdf')->store('lectures/pdfs', 'public');
                $lecture->pdf_path = $pdfPath;
            }

            $lecture->created_by = Auth::id();
            $lecture->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Lecture created successfully!',
                'lecture' => $lecture
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating lecture: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getLectures(Request $request)
    {
        $user = auth()->user();
        $query = Lecture::with(['institute', 'session', 'class', 'section', 'course', 'teacher']);

        // Apply filters based on user role and request parameters
        if ($user->hasRole('Super Admin')) {
            if ($request->institute_id) {
                $query->where('institute_id', $request->institute_id);
            }
        } else {
            $query->where('institute_id', $user->institute_id);
        }

        if ($request->session_id) {
            $query->where('session_id', $request->session_id);
        } elseif ($user->hasRole('Student')) {
            // For students, default to their latest session if no filter is applied
            $latestEnrollment = StudentEnrollment::where('student_id', $user->id)
                ->latest('created_at')
                ->first();
            
            if ($latestEnrollment) {
                $query->where('session_id', $latestEnrollment->session_id);
            }
        }

        if ($request->class_id) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->section_id) {
            $query->where('section_id', $request->section_id);
        }

        if ($request->course_id) {
            $query->where('course_id', $request->course_id);
        }
        if ($request->lecture_date) {
            $query->whereDate('lecture_date', $request->lecture_date);
        }

        if ($user->hasRole('Admin') && $request->teacher_id) {
            $query->where('teacher_id', $request->teacher_id);
        } elseif ($user->hasRole('Teacher')) {
            $query->where('teacher_id', $user->id);
        } elseif ($user->hasRole('Student')) {
            // For students, only show lectures for their enrolled courses
            $enrolledCourses = StudentEnrollment::where('student_id', $user->id)
                ->when($request->session_id, function($q) use ($request) {
                    $q->where('session_id', $request->session_id);
                })
                ->pluck('course_id')
                ->toArray();
            
            $query->whereIn('course_id', $enrolledCourses);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        return datatables()->eloquent($query)
            ->addColumn('title', function ($lecture) {
                return $lecture->title;
            })
            ->addColumn('institute', function ($lecture) {
                return $lecture->institute->name ?? 'N/A';
            })
            ->addColumn('session', function ($lecture) {
                return $lecture->session->session_name ?? 'N/A';
            })
            ->addColumn('class', function ($lecture) {
                return $lecture->class->name ?? 'N/A';
            })
            ->addColumn('section', function ($lecture) {
                return $lecture->section->section_name ?? 'N/A';
            })
            ->addColumn('course', function ($lecture) {
                return $lecture->course->course_name ?? 'N/A';
            })
            ->addColumn('teacher', function ($lecture) {
                return $lecture->teacher ? $lecture->teacher->name : 'N/A';
            })
            ->addColumn('lecture_date', function ($lecture) {
                return $lecture->lecture_date ? \Carbon\Carbon::parse($lecture->lecture_date)->format('Y-m-d') : 'N/A';
            })
            
            ->addColumn('action', function ($lecture) use ($user) {
                $buttons = '<div class="btn-group">';
                
                $buttons .= '<a href="' . route('lectures.view', $lecture->id) . '" class="btn btn-sm btn-info view-btn me-1">
                    <i class="fas fa-eye"></i> View
                </a>';
                
                // Only show edit/delete if user has permission
                if ($user->hasRole('Super Admin') || 
                    ($user->hasRole('Admin') && $lecture->institute_id == $user->institute_id) || 
                    ($user->hasRole('Teacher') && $lecture->teacher_id == $user->id)) {
                    
                    $buttons .= '<button class="btn btn-sm btn-primary edit-btn me-1" data-id="' . $lecture->id . '">
                        <i class="fas fa-edit"></i> Edit
                    </button>';
                    
                    $buttons .= '<button class="btn btn-sm btn-danger delete-btn" data-id="' . $lecture->id . '">
                        <i class="fas fa-trash"></i> Delete
                    </button>';
                }
                
                $buttons .= '</div>';
                
                return $buttons;
            })
            ->rawColumns(['action'])
            ->make(true);
    }
    public function view($id)
    {
        $lecture = Lecture::with(['institute', 'session', 'class', 'section', 'course', 'teacher'])
            ->findOrFail($id);

        // Check if student has access to this lecture
        if (auth()->user()->hasRole('Student')) {
            $enrollment = StudentEnrollment::where('student_id', auth()->id())
                ->where('session_id', $lecture->session_id)
                ->where('class_id', $lecture->class_id)
                ->where('section_id', $lecture->section_id)
                ->where('course_id', $lecture->course_id)
                ->exists();

            if (!$enrollment) {
                abort(403, 'You are not enrolled in this course');
            }
        }

        // Get related lectures (same course)
        $relatedLectures = Lecture::where('course_id', $lecture->course_id)
            ->where('id', '!=', $lecture->id)
            ->where('status', 'published')
            ->orderBy('lecture_date', 'desc')
            ->take(5)
            ->get();

        // Get file info
        $fileInfo = [];
        if ($lecture->video_path) {
            $fileInfo['video'] = [
                'size' => $this->formatFileSize(Storage::disk('public')->size($lecture->video_path)),
                'type' => 'video/mp4',
                'url' => Storage::url($lecture->video_path),
                'download_url' => route('lectures.download', ['id' => $lecture->id, 'type' => 'video'])
            ];
        }

        if ($lecture->pdf_path) {
            $fileInfo['pdf'] = [
                'size' => $this->formatFileSize(Storage::disk('public')->size($lecture->pdf_path)),
                'type' => 'application/pdf',
                'url' => Storage::url($lecture->pdf_path),
                'download_url' => route('lectures.download', ['id' => $lecture->id, 'type' => 'pdf'])
            ];
        }

        return view('lectures.view', compact('lecture', 'relatedLectures', 'fileInfo'));
    }

    public function download($id, $type)
{
    $lecture = Lecture::findOrFail($id);
    
    // Check permissions
    if (auth()->user()->hasRole('Student')) {
        $enrollment = StudentEnrollment::where('student_id', auth()->id())
            ->where('course_id', $lecture->course_id)
            ->exists();
            
        if (!$enrollment) {
            abort(403, 'You are not enrolled in this course');
        }
    }

    $filePath = null;
    $fileName = '';

    if ($type === 'video' && $lecture->video_path) {
        $filePath = storage_path('app/public/' . $lecture->video_path);
        $fileName = \Illuminate\Support\Str::slug($lecture->title) . '.mp4';
    } elseif ($type === 'pdf' && $lecture->pdf_path) {
        $filePath = storage_path('app/public/' . $lecture->pdf_path);
        $fileName = \Illuminate\Support\Str::slug($lecture->title) . '.pdf';
    } else {
        abort(404);
    }

    return response()->download($filePath, $fileName);
}

    private function formatFileSize($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            return $bytes . ' bytes';
        } elseif ($bytes == 1) {
            return '1 byte';
        } else {
            return '0 bytes';
        }
    }

    public function edit($id)
    {
        try {
            $lecture = Lecture::with(['institute', 'session', 'class', 'section', 'course', 'teacher'])
                ->findOrFail($id);

            // Check permissions
            if (auth()->user()->hasRole('Teacher') && auth()->user()->id !== $lecture->teacher_id) {
                throw new \Exception('You are not authorized to edit this lecture');
            }

            return response()->json([
                'success' => true,
                'lecture' => $lecture,
                'video_url' => $lecture->video_path ? Storage::url($lecture->video_path) : null,
                'pdf_url' => $lecture->pdf_path ? Storage::url($lecture->pdf_path) : null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'teacher_id' => 'required|exists:users,id',
            'institute_id' => 'required|exists:institutes,id',
            'session_id' => 'required|exists:sessions,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'course_id' => 'required|exists:courses,id',
            'lecture_date' => 'required|date',
            'status' => 'required|in:active,inactive',
            'video' => 'nullable|file|mimes:mp4|max:51200',
            'pdf' => 'nullable|file|mimes:pdf|max:10240'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $lecture = Lecture::findOrFail($id);

            // Check permissions
            if (auth()->user()->hasRole('Teacher') && auth()->user()->id !== $lecture->teacher_id) {
                throw new \Exception('You are not authorized to update this lecture');
            }

            // Verify the teacher is actually enrolled in this course/section
            $isTeacherEnrolled = StudentEnrollment::where([
                'teacher_id' => $request->teacher_id,
                'institute_id' => $request->institute_id,
                'session_id' => $request->session_id,
                'class_id' => $request->class_id,
                'section_id' => $request->section_id,
                'course_id' => $request->course_id
            ])->exists();

            if (!$isTeacherEnrolled) {
                throw new \Exception('The selected teacher is not enrolled in this course/section');
            }

            $lecture->fill($request->only([
                'title',
                'description',
                'teacher_id',
                'institute_id',
                'session_id',
                'class_id',
                'section_id',
                'course_id',
                'lecture_date',
                'status'
            ]));

            // Handle file uploads
            if ($request->hasFile('video')) {
                // Delete old video if exists
                if ($lecture->video_path) {
                    Storage::disk('public')->delete($lecture->video_path);
                }
                $videoPath = $request->file('video')->store('lectures/videos', 'public');
                $lecture->video_path = $videoPath;
            }

            if ($request->hasFile('pdf')) {
                // Delete old pdf if exists
                if ($lecture->pdf_path) {
                    Storage::disk('public')->delete($lecture->pdf_path);
                }
                $pdfPath = $request->file('pdf')->store('lectures/pdfs', 'public');
                $lecture->pdf_path = $pdfPath;
            }

            $lecture->updated_by = Auth::id();
            $lecture->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Lecture updated successfully!',
                'lecture' => $lecture
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating lecture: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $lecture = Lecture::findOrFail($id);

            // Check permissions
            if (auth()->user()->hasRole('Teacher') && auth()->user()->id !== $lecture->teacher_id) {
                throw new \Exception('You are not authorized to delete this lecture');
            }

            // Delete associated files
            if ($lecture->video_path) {
                Storage::disk('public')->delete($lecture->video_path);
            }
            if ($lecture->pdf_path) {
                Storage::disk('public')->delete($lecture->pdf_path);
            }

            $lecture->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Lecture deleted successfully!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error deleting lecture: ' . $e->getMessage()
            ], 500);
        }
    }
}
