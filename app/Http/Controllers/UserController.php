<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Institute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        return view('users.index');
    }

    public function create()
    {
        $institutes = Institute::where('is_active', true)->get();
        $roles = Role::whereNotIn('name', ['Super Admin'])->get();
        return view('admins.create', compact('institutes', 'roles'));
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

        $data = $request->except('profile_image', 'password', 'role');
        $data['password'] = Hash::make($request->password);

        if ($request->hasFile('profile_image')) {
            $data['profile_image'] = $request->file('profile_image')->store('users/profile_images', 'public');
        }

        $user = User::create($data);
        $user->assignRole($request->role);

        return redirect()->route('admins.index')->with('success', 'User created successfully.');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $institutes = Institute::where('is_active', true)->get();
        $roles = Role::whereNotIn('name', ['Super Admin'])->get();
        return view('admins.form', compact('user', 'institutes', 'roles'));
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

        $data = $request->except('profile_image', 'password', 'role');

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('profile_image')) {
            // Delete old profile image if exists
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
            $data['profile_image'] = $request->file('profile_image')->store('users/profile_images', 'public');
        }

        $user->update($data);
        $user->syncRoles($request->role);

        return redirect()->route('admins.index')->with('success', 'User updated successfully.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
        }
        
        $user->delete();
        
        return response()->json(['success' => 'User deleted successfully.']);
    }

    public function getUsers()
    {
        $users = User::with(['institute', 'roles'])->select('users.*');

        return datatables()->of($users)
            ->addColumn('profile_image', function($user) {
                return '<img src="'.$user->profile_image_url.'" width="50" height="50" class="rounded-circle">';
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
                        <a href="'.route('users.edit', $user->id).'" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="'.$user->id.'">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['profile_image', 'status', 'action'])
            ->make(true);
    }
}