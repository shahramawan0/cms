<?php

namespace App\Http\Controllers;

use App\Models\Session;
use App\Models\Institute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
                $badge = $session->status === 'active' ? 'success' : 
                         ($session->status === 'inactive' ? 'warning' : 'secondary');
                $text = ucfirst($session->status);
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
            'status' => 'required|in:active,inactive,archived',
            'description' => 'nullable|string',
            'institute_id' => 'required_if:role,Super Admin|nullable|exists:institutes,id'
        ]);

        // If user is Admin, set their institute_id
        if (Auth::user()->hasRole('Admin')) {
            $request->merge(['institute_id' => Auth::user()->institute_id]);
        }

        $session = Session::create($request->all());

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
            'status' => 'required|in:active,inactive,archived',
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