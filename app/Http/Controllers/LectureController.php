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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Str;
use App\Models\TimeTable;

class LectureController extends Controller
{
    use HasRoles;

    public function index()
    {
        $user = auth()->user();
        $institutes = $user->hasRole('Super Admin') ? Institute::get() : null;

        // For students, get their current session automatically
        if ($user->hasRole('Student')) {
            $currentEnrollment = StudentEnrollment::where('student_id', $user->id)
                ->where('status', 'active')
                ->orderBy('created_at', 'desc')
                ->first();

            if ($currentEnrollment) {
                $currentSession = [
                    'id' => $currentEnrollment->session_id,
                    'name' => $currentEnrollment->session->session_name
                ];
                return view('lectures.index', compact('institutes', 'currentSession'));
            }
        }

        return view('lectures.index', compact('institutes'));
    }

    public function getDropdowns(Request $request)
    {
        try {
            $data = [];
            $user = Auth::user();
            $instituteId = $user->hasRole('Super Admin') ? $request->institute_id : $user->institute_id;

            if (!$instituteId) {
                return response()->json(['error' => 'Institute not specified'], 400);
            }

            // Get all sessions for the institute
            $sessionQuery = DB::table('sessions')
                ->join('student_enrollments', 'sessions.id', '=', 'student_enrollments.session_id')
                ->where('student_enrollments.institute_id', $instituteId)
                ->select('sessions.id', 'sessions.session_name', 'sessions.start_date', 'sessions.end_date')
                ->distinct();

            // Apply role-based filters
            if ($user->hasRole('Teacher')) {
                $sessionQuery->where('student_enrollments.teacher_id', $user->id);
            } elseif ($user->hasRole('Student')) {
                $sessionQuery->where('student_enrollments.student_id', $user->id);
            }

            $data['sessions'] = $sessionQuery->get()
                ->map(function($session) {
                    return [
                        'id' => $session->id,
                        'session_name' => $session->session_name,
                        'start_date' => $session->start_date ? date('Y-m-d', strtotime($session->start_date)) : null,
                        'end_date' => $session->end_date ? date('Y-m-d', strtotime($session->end_date)) : null
                    ];
                });

            // Get classes if session_id is provided
            if ($request->has('session_id')) {
                $classQuery = DB::table('classes')
                    ->join('student_enrollments', 'classes.id', '=', 'student_enrollments.class_id')
                    ->where('student_enrollments.institute_id', $instituteId)
                    ->where('student_enrollments.session_id', $request->session_id)
                    ->select('classes.id', 'classes.name')
                    ->distinct();

                if ($user->hasRole('Teacher')) {
                    $classQuery->where('student_enrollments.teacher_id', $user->id);
                } elseif ($user->hasRole('Student')) {
                    $classQuery->where('student_enrollments.student_id', $user->id);
                }

                $data['classes'] = $classQuery->get();
            }

            // Get sections if class_id is provided
            if ($request->has('class_id')) {
                $sectionQuery = DB::table('sections')
                    ->join('student_enrollments', 'sections.id', '=', 'student_enrollments.section_id')
                    ->where('student_enrollments.institute_id', $instituteId)
                    ->where('student_enrollments.class_id', $request->class_id)
                    ->select('sections.id', 'sections.section_name')
                    ->distinct();

                if ($user->hasRole('Teacher')) {
                    $sectionQuery->where('student_enrollments.teacher_id', $user->id);
                } elseif ($user->hasRole('Student')) {
                    $sectionQuery->where('student_enrollments.student_id', $user->id);
                }

                $data['sections'] = $sectionQuery->get();
            }

            // Get courses if section_id is provided
            if ($request->has('section_id')) {
                $courseQuery = DB::table('courses')
                    ->join('student_enrollments', 'courses.id', '=', 'student_enrollments.course_id')
                    ->where('student_enrollments.institute_id', $instituteId)
                    ->where('student_enrollments.class_id', $request->class_id)
                    ->where('student_enrollments.section_id', $request->section_id)
                    ->select('courses.id', 'courses.course_name')
                    ->distinct();

                if ($user->hasRole('Teacher')) {
                    $courseQuery->where('student_enrollments.teacher_id', $user->id);
                } elseif ($user->hasRole('Student')) {
                    $courseQuery->where('student_enrollments.student_id', $user->id);
                }

                $data['courses'] = $courseQuery->get();
            }

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error("Error in getDropdowns: " . $e->getMessage());
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
        try {
            $request->validate([
                'institute_id' => 'required|exists:institutes,id',
                'session_id' => 'required|exists:sessions,id',
                'class_id' => 'required|exists:classes,id',
                'section_id' => 'required|exists:sections,id',
                'course_id' => 'required|exists:courses,id',
                'teacher_id' => 'required|exists:users,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'lecture_file' => 'required|file|max:10240', // 10MB max
                'video_file' => 'nullable|file|mimes:mp4,webm|max:512000', // 500MB max
                'slot_date' => 'required|date',
                'slot_time' => 'required|string'
            ]);

            $user = auth()->user();
            
            // Check if a lecture already exists for this slot
            $existingLecture = Lecture::where([
                'course_id' => $request->course_id,
                'class_id' => $request->class_id,
                'section_id' => $request->section_id,
                'slot_date' => $request->slot_date,
                'slot_time' => $request->slot_time
            ])->first();

            if ($existingLecture) {
                return response()->json([
                    'error' => 'A lecture already exists for this time slot',
                    'message' => 'This time slot already has a lecture uploaded.'
                ], 422);
            }

            // Handle file uploads
            $filePaths = [];
            
            // Handle lecture file (PDF/DOC)
            if ($request->hasFile('lecture_file')) {
                $file = $request->file('lecture_file');
                $fileName = time() . '_' . Str::slug($request->title) . '.' . $file->getClientOriginalExtension();
                $filePaths['pdf_path'] = $file->storeAs('lectures/documents', $fileName, 'public');
            }

            // Handle video file if present
            if ($request->hasFile('video_file')) {
                $video = $request->file('video_file');
                $videoName = time() . '_' . Str::slug($request->title) . '_video.' . $video->getClientOriginalExtension();
                $filePaths['video_path'] = $video->storeAs('lectures/videos', $videoName, 'public');
            }

            // Create lecture record
            $lecture = new Lecture([
                'title' => $request->title,
                'description' => $request->description,
                'pdf_path' => $filePaths['pdf_path'] ?? null,
                'video_path' => $filePaths['video_path'] ?? null,
                'slot_date' => $request->slot_date,
                'slot_time' => $request->slot_time,
                'lecture_date' => $request->slot_date,
                'status' => 'active',
                'institute_id' => $request->institute_id,
                'session_id' => $request->session_id,
                'class_id' => $request->class_id,
                'section_id' => $request->section_id,
                'course_id' => $request->course_id,
                'teacher_id' => $request->teacher_id,
                'uploaded_by' => $user->id
            ]);

            $lecture->save();

            return response()->json([
                'success' => true,
                'message' => 'Lecture uploaded successfully',
                'lecture' => $lecture
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in lecture store: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'error' => 'Failed to upload lecture',
                'message' => $e->getMessage()
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
                'message' => 'An error occurred while loading the lecture'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $lecture = Lecture::findOrFail($id);

            // Update basic lecture information
            $lecture->title = $request->title;
            $lecture->description = $request->description;
            $lecture->slot_date = $request->slot_date;
            $lecture->slot_time = $request->slot_time;
            $lecture->lecture_date = $request->slot_date;
            $lecture->status = 'active';

            // Handle video file upload if present
            if ($request->hasFile('video_file')) {
                // Delete old video if exists
                if ($lecture->video_path) {
                    Storage::disk('public')->delete($lecture->video_path);
                }
                $video = $request->file('video_file');
                $videoName = time() . '_' . Str::slug($request->title) . '_video.' . $video->getClientOriginalExtension();
                $lecture->video_path = $video->storeAs('lectures/videos', $videoName, 'public');
            }

            // Handle lecture file upload if present
            if ($request->hasFile('lecture_file')) {
                // Delete old file if exists
                if ($lecture->pdf_path) {
                    Storage::disk('public')->delete($lecture->pdf_path);
                }
                $file = $request->file('lecture_file');
                $fileName = time() . '_' . Str::slug($request->title) . '.' . $file->getClientOriginalExtension();
                $lecture->pdf_path = $file->storeAs('lectures/documents', $fileName, 'public');
            }

            $lecture->save();

            return response()->json([
                'success' => true,
                'message' => 'Lecture updated successfully',
                'lecture' => $lecture
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update lecture: ' . $e->getMessage()
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

    public function getCourses(Request $request)
    {
        try {
            $user = auth()->user();
            
            if (!$request->has('session_id')) {
                return response()->json([], 200); // Return empty array instead of error
            }

            $instituteId = $user->hasRole('Super Admin') ? $request->institute_id : $user->institute_id;
            $sessionId = $request->session_id;

            if ($user->hasRole('Super Admin') && !$request->has('institute_id')) {
                return response()->json([], 200); // Return empty array instead of error
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
                return response()->json([], 200); // Return empty array instead of error
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
            return response()->json([], 200); // Return empty array instead of error
        }
    }

    public function getTimeSlots(Request $request)
    {
        try {
            $request->validate([
                'course_id' => 'required|exists:courses,id',
                'class_id' => 'required|exists:classes,id',
                'section_id' => 'required|exists:sections,id',
                'teacher_id' => 'required|exists:users,id'
            ]);

            $user = auth()->user();

            // Verify student enrollment if user is a student
            if ($user->hasRole('Student')) {
                $isEnrolled = StudentEnrollment::where('student_id', $user->id)
                    ->where('course_id', $request->course_id)
                    ->where('class_id', $request->class_id)
                    ->where('section_id', $request->section_id)
                    ->where('status', 'active')
                    ->exists();

                if (!$isEnrolled) {
                    return response()->json(['error' => 'You are not enrolled in this course'], 403);
                }

                // For students, only get slots that have uploaded lectures
                $existingLectures = Lecture::where('course_id', $request->course_id)
                    ->where('class_id', $request->class_id)
                    ->where('section_id', $request->section_id)
                    ->get()
                    ->keyBy(function($lecture) {
                        return $lecture->slot_date . '_' . $lecture->slot_time;
                    });

                if ($existingLectures->isEmpty()) {
                    return response()->json([]);
                }

                // Get only the slots that have lectures
                $slots = TimeTable::where('course_id', $request->course_id)
                    ->where('class_id', $request->class_id)
                    ->where('section_id', $request->section_id)
                    ->whereIn(DB::raw("CONCAT(date, '_', slot_times)"), $existingLectures->keys())
                    ->orderBy('date')
                    ->orderBy('slot_times')
                    ->get();

                // Format slots with lecture information
                $formattedSlots = $slots->map(function ($slot) use ($existingLectures, $request) {
                    $key = $slot->date . '_' . $slot->slot_times;
                    $lecture = $existingLectures->get($key);
                    
                    return [
                        'date' => $slot->date,
                        'slot_times' => $slot->slot_times,
                        'week' => $slot->week,
                        'class_id' => $request->class_id,
                        'section_id' => $request->section_id,
                        'teacher_id' => $request->teacher_id,
                        'status' => 'Uploaded',
                        'lecture_id' => $lecture ? $lecture->id : null
                    ];
                });

                return response()->json($formattedSlots);
            }

            // For non-students, show all slots
            $slots = TimeTable::where('course_id', $request->course_id)
                ->where('class_id', $request->class_id)
                ->where('section_id', $request->section_id)
                ->orderBy('date')
                ->orderBy('slot_times')
                ->get();

            if ($slots->isEmpty()) {
                return response()->json(['error' => 'No time slots found for this course'], 404);
            }

            // Check for existing lectures
            $existingLectures = Lecture::where('course_id', $request->course_id)
                ->where('class_id', $request->class_id)
                ->where('section_id', $request->section_id)
                ->get()
                ->keyBy(function($lecture) {
                    return $lecture->slot_date . '_' . $lecture->slot_time;
                });

            // Format slots with lecture information
            $formattedSlots = $slots->map(function ($slot) use ($existingLectures, $request) {
                $key = $slot->date . '_' . $slot->slot_times;
                $lecture = $existingLectures->get($key);
                
                return [
                    'date' => $slot->date,
                    'slot_times' => $slot->slot_times,
                    'week' => $slot->week,
                    'class_id' => $request->class_id,
                    'section_id' => $request->section_id,
                    'teacher_id' => $request->teacher_id,
                    'status' => $lecture ? 'Uploaded' : 'Available',
                    'lecture_id' => $lecture ? $lecture->id : null
                ];
            });

            return response()->json($formattedSlots);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load time slots',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
