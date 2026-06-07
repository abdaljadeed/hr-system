<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::all();
        $today = Carbon::today();
        $start = $today->copy()->subDays(29);

        $patterns = [
            'EMP-2026-0001' => ['present' => 70, 'late' => 15, 'absent' => 10, 'on_leave' => 5],
            'EMP-2026-0002' => ['present' => 60, 'late' => 20, 'absent' => 10, 'on_leave' => 10],
            'EMP-2026-0003' => ['present' => 75, 'late' => 5,  'absent' => 15, 'on_leave' => 5],
            'EMP-2026-0004' => ['present' => 55, 'late' => 10, 'absent' => 25, 'on_leave' => 10],
            'EMP-2026-0005' => ['present' => 80, 'late' => 5,  'absent' => 10, 'on_leave' => 5],
        ];

        foreach ($employees as $employee) {
            $weights = $patterns[$employee->employee_code]
                ?? ['present' => 70, 'late' => 10, 'absent' => 15, 'on_leave' => 5];

            $pool = [];
            foreach ($weights as $status => $weight) {
                $pool = array_merge($pool, array_fill(0, $weight, $status));
            }

            for ($i = 0; $i < 30; $i++) {
                $date = $start->copy()->addDays($i);

                if ($date->isWeekend()) {
                    continue;
                }

                if ($date->isToday()) {
                    continue;
                }

                $status = $pool[array_rand($pool)];

                $checkIn = null;
                $checkOut = null;
                $workedHours = null;

                if (in_array($status, ['present', 'late'])) {
                    $inHour = $status === 'late' ? rand(9, 10) : rand(7, 8);
                    $inMinute = rand(0, 59);
                    $checkIn = $date->copy()->setTime($inHour, $inMinute, 0);

                    $outHour = rand(16, 18);
                    $outMinute = rand(0, 59);
                    $checkOut = $date->copy()->setTime($outHour, $outMinute, 0);
                    $workedHours = round($checkIn->diffInMinutes($checkOut) / 60, 2);
                }

                Attendance::create([
                    'employee_id' => $employee->id,
                    'date' => $date->toDateString(),
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'worked_hours' => $workedHours,
                    'status' => $status,
                ]);
            }
        }
    }
}
