<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\Classes;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SectionController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today();

        // Get the active session
        $query = Session::query();
        if (!$user->roles->pluck('name')->contains('Super Admin')) {
            $query->where('institute_id', $user->institute_id);
        }
        
        $activeSession = $query->get()->first(function($session) use ($today) {
            $startDate = Carbon::parse($session->start_date);
            $endDate = Carbon::parse($session->end_date);
            
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

        // Get classes only from active session
        $classes = collect();
        if ($activeSession) {
            $classes = Classes::where('session_id', $activeSession->id)->get();
        }

        return view('sections.index', compact('classes'));
    }

    public function getSections()
    {
        $user = Auth::user();
        $today = Carbon::today();
        
        $query = Section::with(['class.session'])
            ->join('classes', 'sections.class_id', '=', 'classes.id')
            ->join('sessions', 'classes.session_id', '=', 'sessions.id')
            ->select('sections.*');
        
        // If user is not a Super Admin, filter by their institute
        if (!$user->roles->pluck('name')->contains('Super Admin')) {
            $query->where('sessions.institute_id', $user->institute_id);
        }
        
        return datatables()->of($query)
            ->addColumn('status', function($section) use ($today) {
                // Get the session dates from the related class
                $startDate = Carbon::parse($section->class->session->start_date);
                $endDate = Carbon::parse($section->class->session->end_date);
                
                // Check if this section's class belongs to the active session
                $isActive = $today->between($startDate, $endDate);
                
                if ($isActive) {
                    // Check if there's another active session with earlier start date
                    $earlierActiveSession = Session::where('id', '!=', $section->class->session_id)
                        ->where('institute_id', $section->class->session->institute_id)
                        ->where('start_date', '<=', $today)
                        ->where('end_date', '>=', $today)
                        ->where('start_date', '<', $section->class->session->start_date)
                        ->exists();
                    
                    $isActive = !$earlierActiveSession;
                }
                
                $badge = $isActive ? 'success' : 'secondary';
                $text = $isActive ? 'Active' : 'Inactive';
                
                return '<span class="badge bg-'.$badge.'">'.$text.'</span>';
            })
            ->addColumn('action', function($section) use ($user) {
                $buttons = '<div class="btn-group">';
                
                // Get the institute_id through relationships
                $instituteId = $section->class->session->institute_id;
                
                // Only show buttons if Super Admin or same institute
                if ($user->roles->pluck('name')->contains('Super Admin') || $instituteId == $user->institute_id) {
                    $buttons .= '
                        <button class="btn btn-sm btn-primary edit-btn me-1" data-id="'.$section->id.'">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="'.$section->id.'">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    ';
                } else {
                    $buttons .= '<span class="text-muted">No actions</span>';
                }
                
                $buttons .= '</div>';
                return $buttons;
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'section_name' => 'required|string|max:255',
            'class_id' => 'required|exists:classes,id',
            'description' => 'nullable|string'
        ]);

        $section = Section::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Section created successfully!'
        ]);
    }

    public function edit($id)
    {
        $section = Section::findOrFail($id);
        return response()->json($section);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'section_name' => 'required|string|max:255',
            'class_id' => 'required|exists:classes,id',
            'description' => 'nullable|string'
        ]);

        $section = Section::findOrFail($id);
        $section->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Section updated successfully!'
        ]);
    }

    public function destroy($id)
    {
        $section = Section::findOrFail($id);
        $section->delete();

        return response()->json([
            'success' => true,
            'message' => 'Section deleted successfully!'
        ]);
    }
}