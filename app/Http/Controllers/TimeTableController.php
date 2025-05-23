<?php

namespace App\Http\Controllers;

use App\Models\TimeTable;
use App\Models\Institute;
use App\Models\Session;
use App\Models\StudentEnrollment;
use App\Models\TimeSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UsersExport;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\TimeTableExport;
use Maatwebsite\Excel\Concerns\FromCollection;


class TimeTableController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $institutes = $user->hasRole('Super Admin') ? Institute::get() : null;

        return view('timetable.index', compact('institutes'));
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

            // Get sessions
            $data['sessions'] = StudentEnrollment::with('session')
                ->where('institute_id', $instituteId)
                ->select('session_id')
                ->distinct()
                ->get()
                ->pluck('session')
                ->filter()
                ->map(function ($session) {
                    return [
                        'id' => $session->id,
                        'name' => $session->session_name
                    ];
                })
                ->values();

            // Get classes if session is provided
            if ($request->has('session_id')) {
                $data['classes'] = StudentEnrollment::with('class')
                    ->where('institute_id', $instituteId)
                    ->where('session_id', $request->session_id)
                    ->select('class_id')
                    ->distinct()
                    ->get()
                    ->pluck('class')
                    ->filter()
                    ->map(function ($class) {
                        return [
                            'id' => $class->id,
                            'name' => $class->name
                        ];
                    })
                    ->values();
            }

            // Get sections if class is provided
            if ($request->has('class_id')) {
                $data['sections'] = StudentEnrollment::with('section')
                    ->where('institute_id', $instituteId)
                    ->where('session_id', $request->session_id)
                    ->where('class_id', $request->class_id)
                    ->select('section_id')
                    ->distinct()
                    ->get()
                    ->pluck('section')
                    ->filter()
                    ->map(function ($section) {
                        return [
                            'id' => $section->id,
                            'name' => $section->section_name
                        ];
                    })
                    ->values();
            }

            // Get courses if section is provided
            if ($request->has('section_id')) {
                $data['courses'] = StudentEnrollment::with('course')
                    ->where('institute_id', $instituteId)
                    ->where('session_id', $request->session_id)
                    ->where('class_id', $request->class_id)
                    ->where('section_id', $request->section_id)
                    ->select('course_id')
                    ->distinct()
                    ->get()
                    ->pluck('course')
                    ->filter()
                    ->map(function ($course) {
                        return [
                            'id' => $course->id,
                            'name' => $course->course_name
                        ];
                    })
                    ->values();
            }

            // Get teachers if course is provided
            if ($request->has('course_id')) {
                $query = StudentEnrollment::with('teacher')
                    ->where('institute_id', $instituteId)
                    ->where('session_id', $request->session_id)
                    ->where('class_id', $request->class_id)
                    ->where('section_id', $request->section_id)
                    ->where('course_id', $request->course_id);

                $data['teachers'] = $query->select('teacher_id')
                    ->distinct()
                    ->get()
                    ->pluck('teacher')
                    ->filter()
                    ->map(function ($teacher) {
                        return [
                            'id' => $teacher->id,
                            'name' => $teacher->name
                        ];
                    })
                    ->values();
            }

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getTimeSlotTemplates(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'session_id' => 'required|exists:sessions,id'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            $query = TimeSlot::where('session_id', $request->session_id);

            if ($request->has('week_number') && $request->week_number) {
                $query->where('week_number', $request->week_number);
            }

            $templates = $query->orderBy('start_time')
                ->get()
                ->map(function ($template) {
                    return [
                        'id' => $template->id,
                        'text' => $template->start_time . ' - ' . $template->end_time .
                            ' (Break: ' . $template->break_start_time . '-' . $template->break_end_time .
                            ', Duration: ' . $template->slot_duration . ' mins)'
                    ];
                });

            return response()->json(['templates' => $templates]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function calculateSlots(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'time_slot_id' => 'required|exists:time_slots,id'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            $template = TimeSlot::findOrFail($request->time_slot_id);

            $slots = $this->calculateTimeSlots(
                $template->start_time,
                $template->end_time,
                $template->break_start_time,
                $template->break_end_time,
                $template->slot_duration
            );

            return response()->json(['slots' => $slots]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function checkAvailability(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'institute_id' => 'required|exists:institutes,id',
                'session_id' => 'required|exists:sessions,id',
                'class_id' => 'required|exists:classes,id',
                'section_id' => 'required|exists:sections,id',
                'course_id' => 'nullable|exists:courses,id',
                'teacher_id' => 'nullable|exists:users,id',
                'date' => 'required|date',
                'week_number' => 'required|integer|min:1|max:4',
                'time_slot_id' => 'required|exists:time_slots,id'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            $template = TimeSlot::findOrFail($request->time_slot_id);
            $allSlots = $this->calculateTimeSlots(
                $template->start_time,
                $template->end_time,
                $template->break_start_time,
                $template->break_end_time,
                $template->slot_duration
            );

            // Get existing timetable entries for the selected date
            $timetableEntries = TimeTable::where('institute_id', $request->institute_id)
                ->where('date', $request->date)
                ->get();

            // Mark occupied slots
            $slotsWithStatus = array_map(function ($slot) use ($timetableEntries, $request) {
                $isOccupied = false;
                $occupiedBy = null;

                foreach ($timetableEntries as $entry) {
                    $entrySlots = explode(',', $entry->slot_times);
                    if (in_array($slot['formatted'], $entrySlots)) {
                        $isOccupied = true;
                        $occupiedBy = [
                            'course' => $entry->course->course_name ?? 'N/A',
                            'teacher' => $entry->teacher->name ?? 'N/A',
                            'class' => $entry->class->name ?? 'N/A',
                            'section' => $entry->section->section_name ?? 'N/A'
                        ];
                        break;
                    }
                }

                return [
                    'start' => $slot['start'],
                    'end' => $slot['end'],
                    'formatted' => $slot['formatted'],
                    'isOccupied' => $isOccupied,
                    'occupiedBy' => $occupiedBy
                ];
            }, $allSlots);

            return response()->json([
                'slots' => $slotsWithStatus,
                'template' => $template
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function loadTimetable(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'institute_id' => 'required|exists:institutes,id',
            'session_id' => 'required|exists:sessions,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'week_number' => 'required|integer|min:1|max:4'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $timeSlot = TimeSlot::where('session_id', $request->session_id)
            ->where('week_number', $request->week_number)
            ->first();

        if (!$timeSlot) {
            return response()->json(['error' => 'No time slot template found for this week'], 404);
        }

        $allSlots = $this->calculateTimeSlots(
            $timeSlot->start_time,
            $timeSlot->end_time,
            $timeSlot->break_start_time,
            $timeSlot->break_end_time,
            $timeSlot->slot_duration
        );

        $slotTimes = array_map(function ($slot) {
            return $slot['formatted'];
        }, $allSlots);

        $timetableEntries = TimeTable::with(['course', 'teacher', 'class', 'section'])
            ->where('institute_id', $request->institute_id)
            ->where('session_id', $request->session_id)
            ->where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->get();

        $entries = [];

        foreach ($timetableEntries as $entry) {
            $slotArray = is_array($entry->slot_times) ? $entry->slot_times : explode(',', $entry->slot_times);
            foreach ($slotArray as $slot) {
                $entries[] = [
                    'date' => $entry->date,
                    'slot_time' => trim($slot),
                    'course' => $entry->course->course_name ?? 'N/A',
                    'teacher' => $entry->teacher->name ?? 'N/A',
                    'class' => $entry->class->name ?? 'N/A',
                    'section' => $entry->section->section_name ?? 'N/A'
                ];
            }
        }

        $now = Carbon::now();
        $startOfWeek = $now->startOfWeek(Carbon::MONDAY);
        $dates = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $dates[] = [
                'date' => $date->format('Y-m-d'),
                'day' => $date->format('l')
            ];
        }

        return response()->json([
            'slots' => $slotTimes,
            'dates' => $dates,
            'entries' => $entries
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'institute_id' => 'required|exists:institutes,id',
            'session_id' => 'required|exists:sessions,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'course_id' => 'required|exists:courses,id',
            'teacher_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'week_number' => 'required|integer|min:1|max:4',
            'time_slot_id' => 'required|exists:time_slots,id',
            'slot_times' => 'required|string',
            'total_slots' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // Check if record exists for same combination (without slot_times check yet)
            $existing = TimeTable::where('institute_id', $request->institute_id)
                ->where('session_id', $request->session_id)
                ->where('class_id', $request->class_id)
                ->where('section_id', $request->section_id)
                ->where('course_id', $request->course_id)
                ->where('teacher_id', $request->teacher_id)
                ->where('date', $request->date)
                ->where('time_slot_id', $request->time_slot_id)
                ->first();

            if ($existing) {
                // Check if slot_times string is exactly same
                if (trim($existing->slot_times) == trim($request->slot_times)) {
                    return response()->json([
                        'error' => 'Duplicate entry',
                        'message' => 'This timetable entry with the same time slot already exists'
                    ], 409);
                } else {
                    // Append the new slot_times only if it's not already present
                    $existingSlotTimesArray = explode(',', $existing->slot_times);
                    $newSlotTimesArray = explode(',', $request->slot_times);

                    $merged = array_unique(array_merge($existingSlotTimesArray, $newSlotTimesArray));
                    $updatedSlotTimes = implode(',', array_map('trim', $merged));

                    $existing->slot_times = $updatedSlotTimes;
                    $existing->updated_by = Auth::id();
                    $existing->save();

                    return response()->json([
                        'success' => true,
                        'message' => 'Slot times updated successfully',
                        'data' => $existing
                    ]);
                }
            }

            // Check for conflicts
            $conflicts = $this->checkForConflicts($request);
            if ($conflicts->isNotEmpty()) {
                return response()->json([
                    'error' => 'Conflict detected',
                    'conflicts' => $conflicts
                ], 409);
            }

            // Create new record
            $timetableSlot = TimeTable::create([
                'institute_id' => $request->institute_id,
                'session_id' => $request->session_id,
                'class_id' => $request->class_id,
                'section_id' => $request->section_id,
                'course_id' => $request->course_id,
                'teacher_id' => $request->teacher_id,
                'time_slot_id' => $request->time_slot_id,
                'date' => $request->date,
                'week_number' => $request->week_number,
                'slot_times' => $request->slot_times,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Time table slot created successfully',
                'data' => $timetableSlot
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create time table slot',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    private function calculateTimeSlots($startTime, $endTime, $breakStart, $breakEnd, $duration)
    {
        $slots = [];
        $current = strtotime($startTime);
        $end = strtotime($endTime);
        $breakStart = strtotime($breakStart);
        $breakEnd = strtotime($breakEnd);

        while ($current < $end) {
            $next = $current + ($duration * 60);

            // Check if current slot overlaps with break time
            if ($current >= $breakStart && $current < $breakEnd) {
                $current = $breakEnd;
                continue;
            }

            // If next time is after break starts and current is before break ends
            if ($next > $breakStart && $current < $breakEnd) {
                $current = $breakEnd;
                continue;
            }

            if ($next <= $end) {
                $slots[] = [
                    'start' => date('H:i', $current),
                    'end' => date('H:i', $next),
                    'formatted' => date('H:i', $current) . ' - ' . date('H:i', $next)
                ];
            }

            $current = $next;
        }

        return $slots;
    }

    public function edit($id)
{
    try {
        $timetable = TimeTable::with(['institute', 'session', 'class', 'section', 'course', 'teacher', 'timeSlot'])
            ->findOrFail($id);

        // Calculate available slots for the time slot template
        $slots = $this->calculateTimeSlots(
            $timetable->timeSlot->start_time,
            $timetable->timeSlot->end_time,
            $timetable->timeSlot->break_start_time,
            $timetable->timeSlot->break_end_time,
            $timetable->timeSlot->slot_duration
        );

        // Get all time slot templates for the session and week
        $templates = TimeSlot::where('session_id', $timetable->session_id)
            ->where('week_number', $timetable->week_number)
            ->orderBy('start_time')
            ->get()
            ->map(function ($template) {
                return [
                    'id' => $template->id,
                    'text' => $template->start_time . ' - ' . $template->end_time .
                        ' (Break: ' . $template->break_start_time . '-' . $template->break_end_time .
                        ', Duration: ' . $template->slot_duration . ' mins)'
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'timetable' => $timetable,
                'slots' => $slots,
                'templates' => $templates,
                'selected_slots' => explode(',', $timetable->slot_times)
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to load timetable data for editing',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function update(Request $request, $id)
{
    $validator = Validator::make($request->all(), [
        'institute_id' => 'required|exists:institutes,id',
        'session_id' => 'required|exists:sessions,id',
        'class_id' => 'required|exists:classes,id',
        'section_id' => 'required|exists:sections,id',
        'course_id' => 'required|exists:courses,id',
        'teacher_id' => 'required|exists:users,id',
        'date' => 'required|date',
        'week_number' => 'required|integer|min:1|max:4',
        'time_slot_id' => 'required|exists:time_slots,id',
        'slot_times' => 'required|string',
        'total_slots' => 'required|integer|min:1'
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    try {
        $timetable = TimeTable::findOrFail($id);

        // Check for conflicts excluding the current entry
        $conflicts = $this->checkForConflicts($request, $id);
        if ($conflicts->isNotEmpty()) {
            return response()->json([
                'error' => 'Conflict detected',
                'conflicts' => $conflicts
            ], 409);
        }

        $timetable->update([
            'institute_id' => $request->institute_id,
            'session_id' => $request->session_id,
            'class_id' => $request->class_id,
            'section_id' => $request->section_id,
            'course_id' => $request->course_id,
            'teacher_id' => $request->teacher_id,
            'time_slot_id' => $request->time_slot_id,
            'date' => $request->date,
            'week_number' => $request->week_number,
            'slot_times' => $request->slot_times,
            'updated_by' => Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Time table slot updated successfully',
            'data' => $timetable
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Failed to update time table slot',
            'message' => $e->getMessage()
        ], 500);
    }
}

public function destroy($id)
{
    try {
        $timetable = TimeTable::findOrFail($id);
        $timetable->delete();

        return response()->json([
            'success' => true,
            'message' => 'Time table slot deleted successfully'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to delete time table slot',
            'error' => $e->getMessage()
        ], 500);
    }
}

private function checkForConflicts(Request $request, $excludeId = null)
{
    $slots = explode(',', $request->slot_times);
    $conflicts = collect();

    // Check for teacher conflicts on the same date
    $teacherQuery = TimeTable::where('teacher_id', $request->teacher_id)
        ->where('date', $request->date)
        ->where(function ($query) use ($slots) {
            foreach ($slots as $slot) {
                $query->orWhere('slot_times', 'like', '%' . trim($slot) . '%');
            }
        })
        ->with(['course', 'class', 'section']);

    if ($excludeId) {
        $teacherQuery->where('id', '!=', $excludeId);
    }

    $teacherConflicts = $teacherQuery->get();

    // Check for class-section conflicts on the same date
    $classQuery = TimeTable::where('class_id', $request->class_id)
        ->where('section_id', $request->section_id)
        ->where('date', $request->date)
        ->where(function ($query) use ($slots) {
            foreach ($slots as $slot) {
                $query->orWhere('slot_times', 'like', '%' . trim($slot) . '%');
            }
        })
        ->with(['course', 'teacher']);

    if ($excludeId) {
        $classQuery->where('id', '!=', $excludeId);
    }

    $classConflicts = $classQuery->get();

    return $teacherConflicts->merge($classConflicts);
}

public function report()
{
    $user = auth()->user();
    $institutes = $user->hasRole('Super Admin') ? Institute::get() : null;
    
    return view('timetable.timeTableReport', compact('institutes'));
}

public function generateReport(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'institute_id' => 'required|exists:institutes,id',
            'session_id' => 'required|exists:sessions,id',
            'week_number' => 'required|integer|min:1|max:4'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Get time slot template for the week
        $timeSlot = TimeSlot::where('session_id', $request->session_id)
            ->where('week_number', $request->week_number)
            ->first();

        if (!$timeSlot) {
            return response()->json(['error' => 'No time slot template found for this week'], 404);
        }

        // Calculate all time slots
        $allSlots = $this->calculateTimeSlots(
            $timeSlot->start_time,
            $timeSlot->end_time,
            $timeSlot->break_start_time,
            $timeSlot->break_end_time,
            $timeSlot->slot_duration
        );

        $slotTimes = array_map(function ($slot) {
            return $slot['formatted'];
        }, $allSlots);

        // Get all classes and sections for this institute and session
        $classes = StudentEnrollment::with('class')
            ->where('institute_id', $request->institute_id)
            ->where('session_id', $request->session_id)
            ->select('class_id')
            ->distinct()
            ->get()
            ->pluck('class')
            ->filter()
            ->values();

        $timetableData = [];

        foreach ($classes as $class) {
            $sections = StudentEnrollment::with('section')
                ->where('institute_id', $request->institute_id)
                ->where('session_id', $request->session_id)
                ->where('class_id', $class->id)
                ->select('section_id')
                ->distinct()
                ->get()
                ->pluck('section')
                ->filter()
                ->values();

            foreach ($sections as $section) {
                $entries = TimeTable::with(['course', 'teacher', 'class', 'section'])
                    ->where('institute_id', $request->institute_id)
                    ->where('session_id', $request->session_id)
                    ->where('class_id', $class->id)
                    ->where('section_id', $section->id)
                    ->get();

                $formattedEntries = [];
                
                foreach ($entries as $entry) {
                    $slotArray = is_array($entry->slot_times) ? $entry->slot_times : explode(',', $entry->slot_times);
                    foreach ($slotArray as $slot) {
                        $formattedEntries[] = [
                            'date' => $entry->date,
                            'slot_time' => trim($slot),
                            'course' => $entry->course->course_name ?? 'N/A',
                            'teacher' => $entry->teacher->name ?? 'N/A',
                            'class' => $entry->class->name ?? 'N/A',
                            'section' => $entry->section->section_name ?? 'N/A'
                        ];
                    }
                }

                $timetableData[] = [
                    'class' => $class->name,
                    'section' => $section->section_name,
                    'entries' => $formattedEntries
                ];
            }
        }

        $now = Carbon::now();
        $startOfWeek = $now->startOfWeek(Carbon::MONDAY);
        $dates = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $dates[] = [
                'date' => $date->format('Y-m-d'),
                'day' => $date->format('l')
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'slots' => $slotTimes,
                'dates' => $dates,
                'timetableData' => $timetableData,
                'timeSlot' => $timeSlot
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

public function exportReport(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'institute_id' => 'required|exists:institutes,id',
            'session_id' => 'required|exists:sessions,id',
            'week_number' => 'required|integer|min:1|max:4',
            'format' => 'required|in:csv,xlsx,pdf'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Get the report data
        $reportData = $this->getExportData($request);

        if ($request->format === 'pdf') {
            $pdf = PDF::loadView('timetable.exportpdf', [
                'groupedData' => $reportData['groupedData'],
                'weekNumber' => $request->week_number
            ])->setPaper('a4', 'landscape');
            
            return $pdf->download('timetable_week_'.$request->week_number.'.pdf');
        }

        $export = new TimeTableExport($reportData['flatData']);
        
        if ($request->format === 'csv') {
            return Excel::download($export, 'timetable_week_'.$request->week_number.'.csv');
        }

        return Excel::download($export, 'timetable_week_'.$request->week_number.'.xlsx');

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

private function getExportData($request)
{
    // Get time slot template for the week
    $timeSlot = TimeSlot::where('session_id', $request->session_id)
        ->where('week_number', $request->week_number)
        ->firstOrFail();

    // Get all timetable entries for the criteria
    $entries = TimeTable::with(['course', 'teacher', 'class', 'section', 'timeSlot'])
    ->where('institute_id', $request->institute_id)
    ->where('session_id', $request->session_id)
    ->whereHas('timeSlot', function ($query) use ($request) {
        $query->where('week_number', $request->week_number);
    })
    ->get();


    $flatData = [];
    $groupedData = [];

    foreach ($entries as $entry) {
        $slotArray = is_array($entry->slot_times) ? $entry->slot_times : explode(',', $entry->slot_times);
        
        foreach ($slotArray as $slot) {
            $day = Carbon::parse($entry->date)->format('l');
            $slot = trim($slot);
            
            $flatData[] = [
                'Class' => $entry->class->name ?? 'N/A',
                'Section' => $entry->section->section_name ?? 'N/A',
                'Date' => $entry->date,
                'Day' => $day,
                'Time Slot' => $slot,
                'Course' => $entry->course->course_name ?? 'N/A',
                'Teacher' => $entry->teacher->name ?? 'N/A'
            ];

            if (!isset($groupedData[$entry->class->name][$entry->section->section_name])) {
                $groupedData[$entry->class->name][$entry->section->section_name] = [];
            }

            $groupedData[$entry->class->name][$entry->section->section_name][] = [
                'date' => $entry->date,
                'day' => $day,
                'slot' => $slot,
                'course' => $entry->course->course_name ?? 'N/A',
                'teacher' => $entry->teacher->name ?? 'N/A'
            ];
        }
    }

    // Sort grouped data by date and time slot
    foreach ($groupedData as $className => $sections) {
        foreach ($sections as $sectionName => $entries) {
            usort($groupedData[$className][$sectionName], function($a, $b) {
                $dateCompare = strcmp($a['date'], $b['date']);
                if ($dateCompare !== 0) return $dateCompare;
                return strcmp($a['slot'], $b['slot']);
            });
        }
    }

    return [
        'flatData' => $flatData,
        'groupedData' => $groupedData,
        'timeSlot' => $timeSlot
    ];
}

    public function slotindex()
    {
        // Get sessions belonging to the user's institute
        $sessions = Session::whereIn('id', function ($query) {
            $query->select('session_id')
                ->from('student_enrollments')
                ->where('institute_id', Auth::user()->institute_id);
        })
            ->pluck('session_name', 'id');
        return view('timetable.add_time_slot', compact('sessions'));
    }

    public function getClassSlots(Request $request)
    {
        $query = TimeSlot::with('session');

        if ($request->has('session_id') && $request->session_id) {
            $query->where('session_id', $request->session_id);
        }

        if ($request->has('week_number') && $request->week_number) {
            $query->where('week_number', $request->week_number);
        }

        $slots = $query->orderBy('start_time')->get();

        $data = [];
        foreach ($slots as $index => $slot) {
            $data[] = [
                'DT_RowIndex' => $index + 1,
                'session_name' => $slot->session->session_name,
                'start_time' => $slot->start_time,
                'end_time' => $slot->end_time,
                'break_time' => $slot->break_start_time . ' - ' . $slot->break_end_time,
                'slot_duration' => $slot->slot_duration . ' mins',
                'week_number' => $slot->week_number ? 'Week ' . $slot->week_number : '-',
                'action' => '<button class="btn btn-sm btn-primary edit-class-slot" data-id="' . $slot->id . '" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger delete-class-slot" data-id="' . $slot->id . '" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>'
            ];
        }

        return response()->json(['data' => $data]);
    }

    public function getSlotDetails($id)
    {
        $slot = TimeSlot::findOrFail($id);
        return response()->json($slot);
    }

    public function storeClassSlot(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|exists:sessions,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'break_start_time' => 'required|date_format:H:i',
            'break_end_time' => 'required|date_format:H:i|after:break_start_time',
            'slot_duration' => 'required|integer|min:5|max:120',
            'week_number' => 'nullable|integer|min:1|max:4'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $slot = TimeSlot::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Class slot created successfully',
                'data' => $slot
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create class slot',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updateClassSlot(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|exists:sessions,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'break_start_time' => 'required|date_format:H:i',
            'break_end_time' => 'required|date_format:H:i|after:break_start_time',
            'slot_duration' => 'required|integer|min:5|max:120',
            'week_number' => 'nullable|integer|min:1|max:4'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $slot = TimeSlot::findOrFail($id);
            $slot->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Class slot updated successfully',
                'data' => $slot
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update class slot',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteClassSlot($id)
    {
        try {
            TimeSlot::findOrFail($id)->delete();
            return response()->json([
                'success' => true,
                'message' => 'Class slot deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete class slot',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
