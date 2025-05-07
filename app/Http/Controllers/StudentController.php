<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Institute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $instituteId = $user->hasRole('Super Admin') ? $request->institute_id : $user->institute_id;

        $request->validate([
            'name' => 'required|string|max:255',
            'father_name' => 'required|string|max:255',
            'cnic' => 'required|string|max:15|unique:users,cnic',
            'roll_number' => 'required|string|max:50|unique:users,roll_number',
            'gender' => 'required|in:Male,Female,Other',
            'dob' => 'required|date',
            'admission_date' => 'required|date',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        try {
            $data = [
                'name' => $request->name,
                'father_name' => $request->father_name,
                'cnic' => $request->cnic,
                'roll_number' => $request->roll_number,
                'gender' => $request->gender,
                'dob' => $request->dob,
                'admission_date' => $request->admission_date,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'password' => Hash::make($request->password),
                'institute_id' => $instituteId,
                'is_active' => true
            ];

            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                $path = $request->file('profile_image')->store('public/student_profile_images');
                $data['profile_image'] = str_replace('public/', '', $path);
            }

            $student = User::create($data);
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
    
    // Format dates properly for display (they're already in Y-m-d format)
    $student->dob = $student->dob;
    $student->admission_date = $student->admission_date;
    
    // Make sure gender is lowercase to match select options
    $student->gender = ucfirst(strtolower($student->gender));
    
    return response()->json($student);
}

    public function update(Request $request, $id)
    {
        $student = User::role('Student')->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'father_name' => 'required|string|max:255',
            'cnic' => 'required|string|max:15|unique:users,cnic,'.$student->id,
            'roll_number' => 'required|string|max:50|unique:users,roll_number,'.$student->id,
            'gender' => 'required|in:Male,Female,Other',
            'dob' => 'required|date',
            'admission_date' => 'required|date',
            'email' => 'required|email|unique:users,email,'.$student->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        try {
            $data = [
                'name' => $request->name,
                'father_name' => $request->father_name,
                'cnic' => $request->cnic,
                'roll_number' => $request->roll_number,
                'gender' => $request->gender,
                'dob' => $request->dob,
                'admission_date' => $request->admission_date,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
            ];

            if ($request->password) {
                $data['password'] = Hash::make($request->password);
            }

            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                // Delete old image if exists
                if ($student->profile_image) {
                    Storage::delete('public/' . $student->profile_image);
                }
                
                $path = $request->file('profile_image')->store('public/student_profile_images');
                $data['profile_image'] = str_replace('public/', '', $path);
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
            
            // Delete profile image if exists
            if ($student->profile_image) {
                Storage::delete('public/' . $student->profile_image);
            }
            
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