<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Annual Leave', 'code' => 'annual', 'default_days' => 21, 'is_paid' => true,  'color' => 'green'],
            ['name' => 'Sick Leave',   'code' => 'sick',   'default_days' => 14, 'is_paid' => true,  'color' => 'yellow'],
            ['name' => 'Unpaid Leave', 'code' => 'unpaid', 'default_days' => 0,  'is_paid' => false, 'color' => 'gray'],
        ];

        foreach ($types as $type) {
            LeaveType::firstOrCreate(['code' => $type['code']], $type);
        }

        $year = now()->year;
        $paidTypes = LeaveType::where('is_paid', true)->get();

        foreach (Employee::all() as $employee) {
            foreach ($paidTypes as $type) {
                LeaveBalance::firstOrCreate(
                    ['employee_id' => $employee->id, 'leave_type_id' => $type->id, 'year' => $year],
                    ['entitled_days' => $type->default_days, 'used_days' => 0]
                );
            }
        }
    }
}
