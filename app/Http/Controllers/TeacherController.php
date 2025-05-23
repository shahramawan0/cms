<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Institute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class TeacherController extends Controller
{
    public function index()
    {
        $user = auth()->user();
    
        if ($user->hasRole('Super Admin')) {
            // Super Admin sees all teachers
            $teachers = User::role('Teacher')->get();
        } elseif ($user->hasRole('Admin')) {
            // Admin sees teachers either in their institute or created by them
            $teachers = User::role('Teacher')
                ->where(function ($query) use ($user) {
                    $query->where('institute_id', $user->institute_id)
                          ->orWhere('created_by', $user->id);
                })
                ->get();
        } elseif ($user->hasRole('Teacher')) {
            // Teacher sees only themselves
            $teachers = User::where('id', $user->id)->get(); // This should work correctly
        } else {
            // Other roles see nothing or you can throw error
            $teachers = collect();
        }
    
        return view('teacher.index', compact('teachers'));
    }
    

    public function getTeachers()
    {
        $query = User::role('Teacher')->with(['institute', 'admin']);

        if (auth()->user()->hasRole('Admin')) {
            $query->where('institute_id', auth()->user()->institute_id);
        }

        return datatables()->of($query)
            ->addColumn('profile_image', function($teacher) {
                return $teacher->profile_image 
                    ? '<img src="'.asset('storage/'.$teacher->profile_image).'" width="50" height="50" class="rounded-circle">'
                    : '<div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px;">
                        <i class="fas fa-user text-white"></i>
                      </div>';
            })
            ->addColumn('institute', function($teacher) {
                return $teacher->institute ? $teacher->institute->name : 'N/A';
            })
            ->addColumn('admin', function($teacher) {
                return $teacher->admin ? $teacher->admin->name : 'N/A';
            })
            ->addColumn('status', function($teacher) {
                return $teacher->email_verified_at 
                    ? '<span class="badge bg-success">Verified</span>'
                    : '<span class="badge bg-warning text-dark">Unverified</span>';
            })
            ->addColumn('action', function($teacher) {
                return '
                    <div class="btn-group">
                        <button class="btn btn-sm btn-primary edit-btn me-1" data-id="'.$teacher->id.'">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="'.$teacher->id.'">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['profile_image', 'institute', 'admin', 'status', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'institute_id' => 'required|exists:institutes,id',
            'admin_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'gender' => 'required|in:male,female,other',
            'dob' => 'required|date',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'address' => 'nullable|string',
            'designation' => 'nullable|string|max:100',
            'qualification' => 'required|string|max:255',
            'experience_years' => 'required|integer|min:0',
            'specialization' => 'required|string|max:255',
            'joining_date' => 'required|date',
            'salary' => 'required|numeric|min:0',
            'account_title' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $data = $request->except('profile_image', 'password');
            $data['password'] = Hash::make($request->password);

            if ($request->hasFile('profile_image')) {
                $data['profile_image'] = $request->file('profile_image')->store('users/profile_images', 'public');
            }

            $teacher = User::create($data);
            $teacher->assignRole('Teacher');

            return response()->json([
                'success' => true,
                'message' => 'Teacher created successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating teacher: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        $teacher = User::findOrFail($id);
        return response()->json($teacher);
    }

    public function update(Request $request, $id)
    {
        $teacher = User::findOrFail($id);

        $request->validate([
            'institute_id' => 'required|exists:institutes,id',
            'admin_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$teacher->id,
            'phone' => 'nullable|string|max:20',
            'gender' => 'required|in:male,female,other',
            'dob' => 'required|date',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'address' => 'nullable|string',
            'designation' => 'nullable|string|max:100',
            'qualification' => 'required|string|max:255',
            'experience_years' => 'required|integer|min:0',
            'specialization' => 'required|string|max:255',
            'joining_date' => 'required|date',
            'salary' => 'required|numeric|min:0',
            'account_title' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        try {
            $data = $request->except('profile_image', 'password');

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            if ($request->hasFile('profile_image')) {
                if ($teacher->profile_image) {
                    Storage::disk('public')->delete($teacher->profile_image);
                }
                $data['profile_image'] = $request->file('profile_image')->store('users/profile_images', 'public');
            }

            $teacher->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Teacher updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating teacher: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $teacher = User::findOrFail($id);
            
            if ($teacher->profile_image) {
                Storage::disk('public')->delete($teacher->profile_image);
            }
            
            $teacher->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Teacher deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting teacher: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getAdminsByInstitute($institute_id)
    {
        $admins = User::where('institute_id', $institute_id)
                     ->role('Admin')
                     ->get();
                     
        return response()->json(['admins' => $admins]);
    }
}