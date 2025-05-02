<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function showAssignForm(Request $request)
    {
        $roles = Role::all();
        $permissions = Permission::all();

        // Group permissions by module
        $modules = [];
        foreach ($permissions as $permission) {
            $parts = explode(' ', $permission->name);
            if (count($parts) === 2) {
                $action = $parts[0];
                $module = $parts[1];

                if (!isset($modules[$module])) {
                    $modules[$module] = [
                        'name' => $module,
                        'permissions' => []
                    ];
                }

                $modules[$module]['permissions'][$action] = $permission;
            }
        }

        // Check if role is passed via query parameter
        if ($request->has('role')) {
            $selectedRole = Role::findOrFail($request->role);
            $selectedPermissions = $selectedRole->permissions->pluck('id')->toArray();

            return view('permission.assign_permission', compact(
                'roles',
                'modules',
                'selectedRole',
                'selectedPermissions'
            ));
        }

        return view('permission.assign_permission', compact('roles', 'modules'));
    }

    public function assignPermissions(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);
    
        $role = Role::findById($request->role_id);
        $permissions = Permission::whereIn('id', $request->permissions)->get();
        
        // Add new permissions without removing existing ones
        foreach ($permissions as $permission) {
            if (!$role->hasPermissionTo($permission)) {
                $role->givePermissionTo($permission);
            }
        }
    
        return response()->json([
            'message' => 'New permissions added successfully.',
            'status' => 'success'
        ]);
    }

    public function update(Request $request, $roleId)
    {
        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);
    
        $role = Role::findOrFail($roleId);
        $permissionNames = Permission::whereIn('id', $request->permissions ?? [])->pluck('name')->toArray();
        
        // Sync permissions using Spatie
        $role->syncPermissions($permissionNames);
    
        return response()->json(['message' => 'Permissions updated successfully.']);
    }
}