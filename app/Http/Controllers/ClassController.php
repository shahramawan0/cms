<?php

namespace App\Http\Controllers;

use App\Models\Classes; // Using 'Classes' because 'Class' is a reserved word
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClassController extends Controller
{
    public function index()
    {
        $sessions = Session ::get();
        return view('classes.index', compact('sessions'));
    }

    public function getClasses()
    {
        $classes = Classes::with('session')->select('classes.*');

        return datatables()->of($classes)
            ->addColumn('status', function($class) {
                $badge = $class->status === 'active' ? 'success' : 
                         ($class->status === 'inactive' ? 'warning' : 'secondary');
                $text = ucfirst($class->status);
                return '<span class="badge bg-'.$badge.'">'.$text.'</span>';
            })
            ->addColumn('action', function($class) {
                return '
                    <div class="btn-group">
                        <button class="btn btn-sm btn-primary edit-btn me-1" data-id="'.$class->id.'">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="'.$class->id.'">
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
            'name' => 'required|string|max:255',
            'session_id' => 'required|exists:sessions,id',
            'status' => 'required|in:active,inactive,archived',
            'description' => 'nullable|string'
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
            'status' => 'required|in:active,inactive,archived',
            'description' => 'nullable|string'
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