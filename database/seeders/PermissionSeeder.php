<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define modules with their actions
        $modules = [
            'Role' => ['add', 'edit', 'delete', 'permission access'],
            'Permission' => ['list', 'add'],
            'Institute' => ['list', 'Add', 'edit', 'delete'],
            'Admin User' => ['list', 'add', 'edit', 'delete'],
            'Teacher' => ['list', 'Add', 'edit', 'delete'],
            'Course' => ['list', 'Add', 'edit', 'delete'],
            'Session' => ['list', 'Add', 'edit', 'delete'],
            'Classes' => ['list', 'Add', 'edit', 'delete'],
            'Section' => ['list', 'Add', 'edit', 'delete'],
            'Chart Of Code' => ['list', 'Add', 'edit', 'delete'],
            'Course Enrollment' => ['list', 'enroll course', 'unassign course'],
            'Lecture' => ['list', 'Add', 'edit', 'apply filter','delete'],
            'Attendance' => ['list', 'mark attendance', 'edit', 'delete'],
            'Time Table' => ['time slots list', 'add slots', 'edit slots', 'delete slots','time table list','generate report'],
            'Student' => ['list', 'Add', 'edit', 'delete','enroll list','add enrollment','edit enrollment','delete enrollment','enrollment report list'],
           

            // Add more modules as needed
        ];

        // Create permissions
        $permissions = [];
        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $permissions[] = Permission::firstOrCreate([
                    'name' => "{$module}.{$action}",
                    'guard_name' => 'web'
                ]);
            }
        }

        // Create or find Super Admin role and assign all permissions
        $superAdmin = Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'web'
        ]);
        
        $superAdmin->givePermissionTo($permissions);
    }
}