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

        // Define LMS modules
        $modules = [
            'user',
            'role',
            'permission',
            'course',
            'category',
            'lesson',
            'quiz',
            'question',
            'assignment',
            'submission',
            'enrollment',
            'payment',
            'report',
            'certificate',
            'discussion',
            'announcement',
            'attendance',
            'grade',
            'schedule',
            'notification',
            'setting',
            'faq',
            'support_ticket',
            'feedback'
        ];

        $actions = ['view', 'add', 'edit', 'delete'];

        // Create permissions
        foreach ($modules as $module) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => "{$action} {$module}"]);
            }
        }

        // Create Super Admin role and assign all permissions
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdmin->givePermissionTo(Permission::all());
    }
}
