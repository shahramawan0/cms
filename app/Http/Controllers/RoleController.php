<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $roles = Role::select('id', 'name')->get();
            return response()->json(['data' => $roles]);
        }
        
        return view('roles.index');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:roles,name']);
        Role::create(['name' => $request->name]);

        return response()->json(['success' => 'Role created successfully.']);
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);
        return response()->json($role);
    }

    public function update(Request $request, $id)
    {
        $request->validate(['name' => 'required|unique:roles,name,' . $id]);
        $role = Role::findOrFail($id);
        $role->update(['name' => $request->name]);

        return response()->json(['success' => 'Role updated successfully.']);
    }

    public function destroy($id)
    {
        Role::findOrFail($id)->delete();
        return response()->json(['success' => 'Role deleted successfully.']);
    }
}