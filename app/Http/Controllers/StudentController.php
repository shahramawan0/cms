<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Institute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class StudentController extends Controller
{
    public function index()
    {
        return view('student.index');
    }

    public function getStudents()
    {
        $user = auth()->user();
        $query = User::role('Student')->with('institute');

        if ($user->hasRole('Admin')) {
            $query->where('institute_id', $user->institute_id);
        }

        return datatables()->of($query)
            ->addColumn('institute', function($student) {
                return $student->institute ? $student->institute->name : 'N/A';
            })
            // ->addColumn('status', function($student) {
            //     return $student->is_active 
            //         ? '<span class="badge bg-success">Active</span>'
            //         : '<span class="badge bg-danger">Inactive</span>';
            // })
            ->addColumn('action', function($student) {
                return '
                    <div class="btn-group">
                        <button class="btn btn-sm btn-primary edit-btn me-1" data-id="'.$student->id.'">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="'.$student->id.'">
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
        $user = auth()->user();
        $instituteId = $user->hasRole('Super Admin') ? $request->institute_id : $user->institute_id;

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        try {
            $student = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'password' => Hash::make($request->password),
                'institute_id' => $instituteId,
                'is_active' => true
            ]);

            $student->assignRole('Student');

            return response()->json([
                'success' => true,
                'message' => 'Student created successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating student: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        $student = User::role('Student')->findOrFail($id);
        return response()->json($student);
    }

    public function update(Request $request, $id)
    {
        $student = User::role('Student')->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$student->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        try {
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
            ];

            if ($request->password) {
                $data['password'] = Hash::make($request->password);
            }

            // Super admin can change institute, admin cannot
            if (auth()->user()->hasRole('Super Admin') && $request->institute_id) {
                $data['institute_id'] = $request->institute_id;
            }

            $student->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Student updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating student: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $student = User::role('Student')->findOrFail($id);
            $student->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Student deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting student: ' . $e->getMessage()
            ], 500);
        }
    }
}