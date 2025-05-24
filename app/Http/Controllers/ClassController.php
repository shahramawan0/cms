<?php

namespace App\Http\Controllers;

use App\Models\Classes; // Using 'Classes' because 'Class' is a reserved word
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ClassController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today();

        // Get all sessions for the institute
        $query = Session::query();
        if (!$user->roles->pluck('name')->contains('Super Admin')) {
            $query->where('institute_id', $user->institute_id);
        }
        $sessions = $query->get();

        // Find the active session
        $activeSession = $sessions->first(function($session) use ($today) {
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

        return view('classes.index', compact('sessions', 'activeSession'));
    }

    public function getClasses()
    {
        $user = Auth::user();
        $today = Carbon::today();

        // Start the query with join to sessions table
        $query = Classes::with('session')
            ->join('sessions', 'classes.session_id', '=', 'sessions.id')
            ->select('classes.*');

        // If user is not a Super Admin, filter by their institute
        if (!$user->hasRole('Super Admin')) {
            $query->where('sessions.institute_id', $user->institute_id);
        }

        return datatables()->of($query)
            ->addColumn('status', function ($class) use ($today) {
                // Get the session dates
                $startDate = Carbon::parse($class->session->start_date);
                $endDate = Carbon::parse($class->session->end_date);
                
                // Check if this class belongs to the active session
                $isActive = $today->between($startDate, $endDate);
                
                if ($isActive) {
                    // Check if there's another active session with earlier start date
                    $earlierActiveSession = Session::where('id', '!=', $class->session_id)
                        ->where('institute_id', $class->session->institute_id)
                        ->where('start_date', '<=', $today)
                        ->where('end_date', '>=', $today)
                        ->where('start_date', '<', $class->session->start_date)
                        ->exists();
                    
                    $isActive = !$earlierActiveSession;
                }
                
                $badge = $isActive ? 'success' : 'secondary';
                $text = $isActive ? 'Active' : 'Inactive';
                
                return '<span class="badge bg-'.$badge.'">'.$text.'</span>';
            })
            ->addColumn('name_with_background_color', function ($class) {
                return '<div class="d-flex align-items-center">
                    <div class="color-box me-2" style="width: 20px; height: 20px; border-radius: 4px; background-color: '.$class->background_color.'"></div>
                    '.$class->name.'
                </div>';
            })
            ->addColumn('action', function ($class) {
                return '
                <div class="btn-group">
                    <button class="btn btn-sm btn-primary edit-btn me-1" data-id="' . $class->id . '">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-sm btn-danger delete-btn" data-id="' . $class->id . '">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            ';
            })
            ->rawColumns(['status', 'name_with_background_color', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'session_id' => 'required|exists:sessions,id',
            'description' => 'nullable|string',
            'background_color' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/' // Validate hex color
        ]);

        $class = Classes::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Class created successfully!'
        ]);
    }

    public function edit($id)
    {
        $class = Classes::findOrFail($id);
        return response()->json($class);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'session_id' => 'required|exists:sessions,id',
            'description' => 'nullable|string',
            'background_color' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/' // Validate hex color
        ]);

        $class = Classes::findOrFail($id);
        $class->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Class updated successfully!'
        ]);
    }

    public function destroy($id)
    {
        $class = Classes::findOrFail($id);
        $class->delete();

        return response()->json([
            'success' => true,
            'message' => 'Class deleted successfully!'
        ]);
    }
}
