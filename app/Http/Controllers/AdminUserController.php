<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Institute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class AdminUserController extends Controller
{
    private function getAdminRoles()
    {
        return Role::whereIn('name', ['Admin', 'Institute Admin'])->get();
    }

    public function index()
    {
        $institutes = Institute::where('is_active', true)->get();
        $roles = $this->getAdminRoles();
        return view('admins.index', compact('institutes', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'institute_id' => 'required|exists:institutes,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'designation' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|exists:roles,id'
        ]);

        $selectedRole = Role::findOrFail($request->role);
        if (!in_array($selectedRole->name, ['Admin', 'Institute Admin'])) {
            return back()->with('error', 'Only admin roles can be assigned');
        }

        $data = $request->except('profile_image', 'password', 'role');
        $data['password'] = Hash::make($request->password);

        if ($request->hasFile('profile_image')) {
            $data['profile_image'] = $request->file('profile_image')->store('users/profile_images', 'public');
        }

        $user = User::create($data);
        $user->assignRole($selectedRole->name);

        return response()->json(['success' => true, 'message' => 'Admin user created successfully.']);
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $institutes = Institute::where('is_active', true)->get();
        $roles = $this->getAdminRoles();
        return response()->json(['user' => $user, 'institutes' => $institutes, 'roles' => $roles]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'institute_id' => 'required|exists:institutes,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:20',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'designation' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|exists:roles,id'
        ]);

        $selectedRole = Role::findOrFail($request->role);
        if (!in_array($selectedRole->name, ['Admin', 'Institute Admin'])) {
            return back()->with('error', 'Only admin roles can be assigned');
        }

        $data = $request->except('profile_image', 'password', 'role');

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
            $data['profile_image'] = $request->file('profile_image')->store('users/profile_images', 'public');
        }

        $user->update($data);
        $user->syncRoles([$selectedRole->name]);

        return response()->json(['success' => true, 'message' => 'Admin user updated successfully.']);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
        }
        
        $user->delete();
        
        return response()->json(['success' => 'Admin user deleted successfully.']);
    }

    public function getUsers()
    {
        $adminRoles = $this->getAdminRoles()->pluck('name');
        
        $users = User::with(['institute', 'roles'])
            ->whereHas('roles', function($q) use ($adminRoles) {
                $q->whereIn('name', $adminRoles);
            });

        return datatables()->of($users)
            ->addColumn('profile_image', function($user) {
                return $user->profile_image 
                    ? '<img src="'.asset('storage/'.$user->profile_image).'" width="50" height="50" class="rounded-circle">'
                    : '<div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px;">
                          <i class="fas fa-user text-white"></i>
                       </div>';
            })
            ->addColumn('institute', function($user) {
                return $user->institute ? $user->institute->name : 'N/A';
            })
            ->addColumn('role', function($user) {
                return $user->roles->first()->name ?? 'N/A';
            })
            ->addColumn('status', function($user) {
                return $user->email_verified_at 
                    ? '<span class="badge bg-success">Verified</span>'
                    : '<span class="badge bg-warning text-dark">Unverified</span>';
            })
            ->addColumn('action', function($user) {
                return '
                    <div class="btn-group">
                        <button class="btn btn-sm btn-info edit-btn me-1" data-id="'.$user->id.'">
                            <i class="fas fa-edit"></i>
                            Edit
                        </button>
                        <button class="btn btn-sm btn-danger delete-btn me-1" data-id="'.$user->id.'">
                            <i class="fas fa-trash"></i>
                            Delete
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['profile_image', 'status', 'action'])
            ->make(true);
    }
}
