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
        return view('teacher.index');
    }

    public function create()
    {
        $institutes = Institute::where('is_active', true)->get();
        
        // Get admins based on current user role
        if (auth()->user()->hasRole('Super Admin')) {
          
            $admins = User::role(['Admin'])->get();
        } else {
            $admins = User::where('id', auth()->id())->get();
        }

        return view('teacher.create', compact('institutes', 'admins'));
    }
    public function getAdminsByInstitute($institute_id)
{
    // Find the institute and fetch admins associated with it
    $admins = User::where('institute_id', $institute_id)->whereHas('roles', function ($query) {
        $query->where('name', 'Admin');
    })->get();

    // Return a JSON response with the admins
    return response()->json(['admins' => $admins]);
}

    public function store(Request $request)
    {
        $request->validate([
            'institute_id' => 'required|exists:institutes,id',
            'admin_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'designation' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'qualification' => 'required|string|max:255',
            'experience_years' => 'required|integer|min:0',
            'specialization' => 'required|string|max:255',
            'joining_date' => 'required|date',
            'salary' => 'required|numeric|min:0',
            'account_title' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'gender' => 'required|in:male,female,other',
            'dob' => 'required|date',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $data = $request->except('profile_image', 'password');
        $data['password'] = Hash::make($request->password);

        if ($request->hasFile('profile_image')) {
            $data['profile_image'] = $request->file('profile_image')->store('users/profile_images', 'public');
        }

        $teacher = User::create($data);
        $teacher->assignRole('Teacher');

        return redirect()->route('admin.teachers.index')->with('success', 'Teacher created successfully.');
    }

    public function edit($id)
    {
        $teacher = User::with(['institute'])->findOrFail($id);
        $institutes = Institute::where('is_active', true)->get();
        
        // Get admins based on current user role
        if (auth()->user()->hasRole('Super Admin')) {
            $admins = User::role(['Admin'])->get();
        } else {
            $admins = User::where('id', auth()->id())->get();
        }

        return view('teacher.create', compact('teacher', 'institutes', 'admins'));
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
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'designation' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'qualification' => 'required|string|max:255',
            'experience_years' => 'required|integer|min:0',
            'specialization' => 'required|string|max:255',
            'joining_date' => 'required|date',
            'salary' => 'required|numeric|min:0',
            'account_title' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'gender' => 'required|in:male,female,other',
            'dob' => 'required|date',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

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

        return redirect()->route('admin.teachers.index')->with('success', 'Teacher updated successfully.');
    }

    public function destroy($id)
    {
        $teacher = User::findOrFail($id);
        
        if ($teacher->profile_image) {
            Storage::disk('public')->delete($teacher->profile_image);
        }
        
        $teacher->delete();
        
        return response()->json(['success' => 'Teacher deleted successfully.']);
    }

    public function view($id)
    {
        $teacher = User::with(['institute', 'admin'])->findOrFail($id);
        return view('teacher.view', compact('teacher'));
    }

    // In TeacherController.php, update the getTeachers method:
    public function getTeachers()
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    
        $query = User::role('Teacher')->with(['institute', 'admin']);
    
        if (auth()->user()->hasRole('Super Admin')) {
          
        } 
        elseif (auth()->user()->hasRole('Admin')) {
          
            $query->where('institute_id', auth()->user()->institute_id);
        } 
        elseif (auth()->user()->hasRole('Teacher')) {
            
            $query->where('id', auth()->id());
        } 
        else {
           
            return response()->json(['error' => 'Forbidden'], 403);
        }
    
        return datatables()->of($query)
            ->addColumn('profile_image', function($teacher) {
                return $teacher->profile_image 
                    ? '<img src="'.asset('storage/'.$teacher->profile_image).'" width="50" height="50" class="rounded-circle">'
                    : '<div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px;">
                        <i class="fas fa-user text-white"></i>
                    </div>';
            })
            ->addColumn('name', function($teacher) {
                return $teacher->name ?? 'N/A'; // Name field
            })
            ->addColumn('email', function($teacher) {
                return $teacher->email ?? 'N/A'; // Email field
            })
            ->addColumn('phone', function($teacher) {
                return $teacher->phone ?? 'N/A'; // Phone field
            })
            ->addColumn('institute', function($teacher) {
                return $teacher->institute ? $teacher->institute->name : 'N/A';
            })
            ->addColumn('admin', function($teacher) {
                return $teacher->admin ? $teacher->admin->name : 'N/A';
            })
            ->addColumn('qualification', function($teacher) {
                return $teacher->qualification ?? 'N/A';
            })
            ->addColumn('experience', function($teacher) {
                return $teacher->experience_years ? $teacher->experience_years.' years' : 'N/A';
            })
            ->addColumn('specialization', function($teacher) {
                return $teacher->specialization ?? 'N/A';
            })
            ->addColumn('joining_date', function($teacher) {
                // Ensure that the joining_date is a Carbon instance before applying format
                return $teacher->joining_date instanceof \Carbon\Carbon 
                    ? $teacher->joining_date->format('M d, Y') 
                    : \Carbon\Carbon::parse($teacher->joining_date)->format('M d, Y');
            })
            ->addColumn('salary', function($teacher) {
                return $teacher->salary ? number_format($teacher->salary, 2) : 'N/A';
            })
            ->addColumn('status', function($teacher) {
                return $teacher->email_verified_at 
                    ? '<span class="badge bg-success">Verified</span>'
                    : '<span class="badge bg-warning text-dark">Unverified</span>';
            })
            ->addColumn('action', function($teacher) {
                return '
                    <div class="btn-group">
                        <a href="'.route('admin.teachers.edit', $teacher->id).'" class="btn btn-sm btn-info me-1">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <button class="btn btn-sm btn-danger delete-btn me-1" data-id="'.$teacher->id.'">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                        <a href="'.route('admin.teachers.view', $teacher->id).'" class="btn btn-sm btn-secondary me-1">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </div>
                ';
            })
            ->rawColumns(['profile_image', 'status', 'action'])
            ->make(true);
    }
    
}