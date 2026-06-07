<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'employees.view', 'employees.create', 'employees.update', 'employees.delete',
            'departments.view', 'departments.manage',
            'attendance.view', 'attendance.manage',
            'leaves.view', 'leaves.request', 'leaves.approve',
            'payroll.view', 'payroll.generate',
            'tasks.view', 'tasks.manage', 'tasks.assign',
            'reports.view',
            'activitylog.view',
            'announcements.manage',
            'users.manage', 'roles.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $hrManager = Role::firstOrCreate(['name' => 'HR Manager']);
        $teamLead = Role::firstOrCreate(['name' => 'Team Lead']);
        $employee = Role::firstOrCreate(['name' => 'Employee']);

        $admin->syncPermissions(Permission::all());

        $hrManager->syncPermissions([
            'employees.view', 'employees.create', 'employees.update', 'employees.delete',
            'departments.view', 'departments.manage',
            'attendance.view', 'attendance.manage',
            'leaves.view', 'leaves.approve',
            'payroll.view', 'payroll.generate',
            'tasks.view',
            'reports.view',
            'activitylog.view',
            'announcements.manage',
        ]);

        $teamLead->syncPermissions([
            'employees.view',
            'departments.view',
            'attendance.view',
            'leaves.view', 'leaves.approve',
            'tasks.view', 'tasks.manage', 'tasks.assign',
            'reports.view',
        ]);

        $employee->syncPermissions([
            'attendance.view',
            'leaves.view', 'leaves.request',
            'payroll.view',
            'tasks.view',
        ]);

        $users = [
            ['name' => 'System Admin', 'email' => 'admin@hr.test', 'role' => 'Admin'],
            ['name' => 'Hala HR', 'email' => 'hr@hr.test', 'role' => 'HR Manager'],
            ['name' => 'Tariq Lead', 'email' => 'lead@hr.test', 'role' => 'Team Lead'],
            ['name' => 'Omar Employee', 'email' => 'employee@hr.test', 'role' => 'Employee'],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles([$data['role']]);
        }
    }
}
