<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    private const SUBJECT_TYPES = [
        'App\Models\Employee' => 'Employee',
        'App\Models\LeaveRequest' => 'Leave Request',
        'App\Models\Payroll' => 'Payroll',
        'App\Models\Task' => 'Task',
        'App\Models\Attendance' => 'Attendance',
    ];

    public function index(Request $request): View
    {
        $filters = $request->only(['causer_id', 'subject_type', 'from_date', 'to_date']);

        $activities = Activity::with(['causer', 'subject'])
            ->when($filters['causer_id'] ?? null, fn ($q, $v) => $q->where('causer_id', $v))
            ->when($filters['subject_type'] ?? null, fn ($q, $v) => $q->where('subject_type', $v))
            ->when($filters['from_date'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['to_date'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $causers = User::orderBy('name')->get();

        return view('activity.index', [
            'activities' => $activities,
            'causers' => $causers,
            'subjectTypes' => self::SUBJECT_TYPES,
            'filters' => $filters,
        ]);
    }
}
