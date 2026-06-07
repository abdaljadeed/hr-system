<?php

namespace App\Http\Controllers;

use App\Http\Requests\Attendance\ManualAttendanceRequest;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Services\Attendance\AttendanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class AttendanceController extends Controller
{
    public function __construct(private AttendanceService $attendanceService) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Attendance::class);

        $query = Attendance::with(['employee.department'])
            ->accessibleBy(auth()->user())
            ->orderByDesc('date');

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('department_id')) {
            $query->whereHas('employee', fn ($q) => $q->where('department_id', $request->department_id));
        }

        if ($request->filled('month')) {
            [$year, $month] = explode('-', $request->month);
            $query->forMonth((int) $year, (int) $month);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $attendances = $query->paginate(25)->withQueryString();
        $employees = Employee::orderBy('first_name')->get();
        $departments = Department::orderBy('name')->get();

        return view('attendance.index', compact('attendances', 'employees', 'departments'));
    }

    public function show(Request $request, Employee $employee): View
    {
        $attendance = Attendance::forEmployee($employee->id)->forDate(now()->toDateString())->first();
        $this->authorize('view', $attendance ?? new Attendance(['employee_id' => $employee->id]));

        $year = (int) ($request->year ?? now()->year);
        $month = (int) ($request->month ?? now()->month);

        $report = $this->attendanceService->getMonthlyReport($employee, $year, $month);

        return view('attendance.show', compact('employee', 'year', 'month', 'report'));
    }

    public function checkIn(Employee $employee): RedirectResponse
    {
        $this->authorize('checkIn', [Attendance::class, $employee]);

        try {
            $this->attendanceService->checkIn($employee);

            return back()->with('success', 'Checked in successfully.');
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function checkOut(Employee $employee): RedirectResponse
    {
        $this->authorize('checkOut', [Attendance::class, $employee]);

        try {
            $this->attendanceService->checkOut($employee);

            return back()->with('success', 'Checked out successfully.');
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function store(ManualAttendanceRequest $request): RedirectResponse
    {
        $this->authorize('create', Attendance::class);

        $employee = Employee::findOrFail($request->validated()['employee_id']);

        $this->attendanceService->manualEntry($employee, $request->validated());

        return redirect()->route('attendance.show', $employee)
            ->with('success', 'Attendance record saved.');
    }

    public function update(ManualAttendanceRequest $request, Attendance $attendance): RedirectResponse
    {
        $this->authorize('update', $attendance);

        $this->attendanceService->manualEntry($attendance->employee, $request->validated());

        return back()->with('success', 'Attendance record updated.');
    }
}
