<?php

namespace App\Http\Controllers;

use App\Http\Requests\Leave\ReviewLeaveRequest;
use App\Http\Requests\Leave\StoreLeaveRequest;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Services\Leave\LeaveRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class LeaveRequestController extends Controller
{
    public function __construct(private LeaveRequestService $leaveService) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', LeaveRequest::class);

        $query = LeaveRequest::with(['employee.department', 'leaveType', 'reviewer'])
            ->accessibleBy(auth()->user())
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('leave_type_id')) {
            $query->where('leave_type_id', $request->leave_type_id);
        }

        if ($request->filled('department_id')) {
            $query->whereHas('employee', fn ($q) => $q->where('department_id', $request->department_id));
        }

        $leaveRequests = $query->paginate(20)->withQueryString();
        $leaveTypes = LeaveType::active()->orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        $balances = $this->currentBalances();

        return view('leaves.index', compact('leaveRequests', 'leaveTypes', 'departments', 'balances'));
    }

    public function create(): View
    {
        $this->authorize('create', LeaveRequest::class);

        $leaveTypes = LeaveType::active()->orderBy('name')->get();
        $balances = $this->currentBalances();

        return view('leaves.create', compact('leaveTypes', 'balances'));
    }

    public function store(StoreLeaveRequest $request): RedirectResponse
    {
        $this->authorize('create', LeaveRequest::class);

        $employee = auth()->user()->employee;

        try {
            $this->leaveService->request($employee, $request->validated());
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('leaves.index')->with('success', 'Leave request submitted.');
    }

    public function show(LeaveRequest $leave): View
    {
        $this->authorize('view', $leave);

        $leave->load(['employee.department', 'leaveType', 'reviewer']);

        return view('leaves.show', compact('leave'));
    }

    public function approve(ReviewLeaveRequest $request, LeaveRequest $leave): RedirectResponse
    {
        $this->authorize('approve', $leave);

        try {
            $this->leaveService->approve($leave, auth()->user(), $request->validated()['review_notes'] ?? null);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Leave request approved.');
    }

    public function reject(ReviewLeaveRequest $request, LeaveRequest $leave): RedirectResponse
    {
        $this->authorize('approve', $leave);

        try {
            $this->leaveService->reject($leave, auth()->user(), $request->validated()['review_notes'] ?? null);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Leave request rejected.');
    }

    public function cancel(LeaveRequest $leave): RedirectResponse
    {
        $this->authorize('cancel', $leave);

        try {
            $this->leaveService->cancel($leave);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Leave request cancelled.');
    }

    private function currentBalances()
    {
        $employee = auth()->user()->employee;

        if (! $employee) {
            return collect();
        }

        return $employee->leaveBalances()
            ->with('leaveType')
            ->where('year', now()->year)
            ->get();
    }
}
