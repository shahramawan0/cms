<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function assignForm()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();
        
        // Group permissions by module
        $modules = [];
        foreach ($permissions as $permission) {
            $parts = explode(' ', $permission->name);
            if (count($parts) === 2) {
                $module = $parts[1];
                $action = $parts[0];
                
                if (!isset($modules[$module])) {
                    $modules[$module] = [
                        'name' => $module,
                        'permissions' => []
                    ];
                }
                
                $modules[$module]['permissions'][$action] = $permission;
            }
        }

        return view('permission.assign_permission', compact('roles', 'modules'));
    }

    public function updatePermissions(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $role->syncPermissions($request->permissions);
        
        return redirect()->back()->with('success', 'Permissions updated successfully');
    }
}