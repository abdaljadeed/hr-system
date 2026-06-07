<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;

class DepartmentEmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $engineering = Department::create([
            'name' => 'Engineering',
            'code' => 'ENG',
            'description' => 'Software development and infrastructure.',
        ]);

        $hr = Department::create([
            'name' => 'Human Resources',
            'code' => 'HR',
            'description' => 'People operations, recruitment and compliance.',
        ]);

        $ops = Department::create([
            'name' => 'Operations',
            'code' => 'OPS',
            'description' => 'Business operations and project delivery.',
        ]);

        $hrUser = User::where('email', 'hr@hr.test')->first();
        $leadUser = User::where('email', 'lead@hr.test')->first();
        $empUser = User::where('email', 'employee@hr.test')->first();

        $halaHR = Employee::create([
            'user_id' => $hrUser?->id,
            'department_id' => $hr->id,
            'employee_code' => 'EMP-2026-0001',
            'first_name' => 'Hala',
            'last_name' => 'HR',
            'phone' => '+966 50 000 0001',
            'gender' => 'female',
            'date_of_birth' => '1990-03-15',
            'job_title' => 'HR Manager',
            'hire_date' => '2022-01-10',
            'employment_status' => 'active',
            'base_salary' => 12000.00,
        ]);

        $tariqLead = Employee::create([
            'user_id' => $leadUser?->id,
            'department_id' => $engineering->id,
            'employee_code' => 'EMP-2026-0002',
            'first_name' => 'Tariq',
            'last_name' => 'Lead',
            'phone' => '+966 50 000 0002',
            'gender' => 'male',
            'date_of_birth' => '1988-07-22',
            'job_title' => 'Engineering Lead',
            'hire_date' => '2021-05-01',
            'employment_status' => 'active',
            'base_salary' => 15000.00,
        ]);

        $omarEmp = Employee::create([
            'user_id' => $empUser?->id,
            'department_id' => $engineering->id,
            'employee_code' => 'EMP-2026-0003',
            'first_name' => 'Omar',
            'last_name' => 'Employee',
            'phone' => '+966 50 000 0003',
            'gender' => 'male',
            'date_of_birth' => '1995-11-08',
            'job_title' => 'Software Developer',
            'hire_date' => '2023-03-15',
            'employment_status' => 'active',
            'base_salary' => 9000.00,
        ]);

        Employee::create([
            'user_id' => null,
            'department_id' => $ops->id,
            'employee_code' => 'EMP-2026-0004',
            'first_name' => 'Sara',
            'last_name' => 'Al-Omari',
            'phone' => '+966 50 000 0004',
            'gender' => 'female',
            'date_of_birth' => '1993-04-20',
            'job_title' => 'Operations Analyst',
            'hire_date' => '2023-09-01',
            'employment_status' => 'probation',
            'base_salary' => 8500.00,
        ]);

        Employee::create([
            'user_id' => null,
            'department_id' => $engineering->id,
            'employee_code' => 'EMP-2026-0005',
            'first_name' => 'Khalid',
            'last_name' => 'Al-Rashid',
            'phone' => '+966 50 000 0005',
            'gender' => 'male',
            'date_of_birth' => '1991-09-12',
            'job_title' => 'Backend Developer',
            'hire_date' => '2022-11-15',
            'employment_status' => 'active',
            'base_salary' => 10000.00,
        ]);

        $engineering->update(['manager_id' => $tariqLead->id]);
        $hr->update(['manager_id' => $halaHR->id]);
    }
}
