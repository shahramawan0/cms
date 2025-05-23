<?php

namespace App\Http\Controllers;

use App\Models\Session;
use App\Models\Institute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SessionController extends Controller
{
    public function index()
    {
        $institutes = [];
        
        if (Auth::user()->hasRole('Super Admin')) {
            $institutes = Institute::get();
        }
        
        return view('session.index', compact('institutes'));
    }

    public function getSessions()
    {
        $sessions = Session::query();
        
        // If user is Admin, filter by their institute
        if (Auth::user()->hasRole('Admin')) {
            $sessions->where('institute_id', Auth::user()->institute_id);
        }

        return datatables()->of($sessions)
            ->addColumn('status', function($session) {
                $today = Carbon::today();
                $startDate = Carbon::parse($session->start_date);
                $endDate = Carbon::parse($session->end_date);
                
                $isActive = $today->between($startDate, $endDate);
                
                // Check if there's another active session with earlier start date
                if ($isActive) {
                    $earlierActiveSessions = Session::where('id', '!=', $session->id)
                        ->where('institute_id', $session->institute_id)
                        ->where('start_date', '<=', $today)
                        ->where('end_date', '>=', $today)
                        ->where('start_date', '<', $session->start_date)
                        ->exists();
                    
                    // If there's an earlier active session, this one should be inactive
                    if ($earlierActiveSessions) {
                        $isActive = false;
                    }
                }
                
                $badge = $isActive ? 'success' : 'secondary';
                $text = $isActive ? 'Active' : 'Inactive';
                
                return '<span class="badge bg-'.$badge.'">'.$text.'</span>';
            })
            ->addColumn('action', function($session) {
                return '
                    <div class="btn-group">
                        <button class="btn btn-sm btn-primary edit-btn me-1" data-id="'.$session->id.'">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="'.$session->id.'">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'session_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'institute_id' => 'required_if:role,Super Admin|nullable|exists:institutes,id'
        ]);

        // If user is Admin, set their institute_id
        if (Auth::user()->hasRole('Admin')) {
            $request->merge(['institute_id' => Auth::user()->institute_id]);
        }

        $today = Carbon::today();
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        // Check for any existing session that hasn't ended yet
        $existingSession = Session::where('institute_id', $request->institute_id)
            ->where('end_date', '>=', $today)
            ->exists();

        // Create the session
        $session = Session::create($request->all());

        // If there's an existing session that hasn't ended yet and new session includes today or future dates
        if ($existingSession && $endDate->greaterThanOrEqualTo($today)) {
            return response()->json([
                'success' => true,
                'message' => 'Session added successfully, but currently marked as Inactive because another session is already active for the current date.'
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Session created successfully!'
        ]);
    }

    public function edit($id)
    {
        $session = Session::findOrFail($id);
        return response()->json($session);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'session_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'institute_id' => 'required_if:role,Super Admin|nullable|exists:institutes,id'
        ]);

        // If user is Admin, set their institute_id
        if (Auth::user()->hasRole('Admin')) {
            $request->merge(['institute_id' => Auth::user()->institute_id]);
        }

        $session = Session::findOrFail($id);
        $session->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Session updated successfully!'
        ]);
    }

    public function destroy($id)
    {
        $session = Session::findOrFail($id);
        $session->delete();

        return response()->json([
            'success' => true,
            'message' => 'Session deleted successfully!'
        ]);
    }
}