<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payroll\AddPayrollItemRequest;
use App\Http\Requests\Payroll\GeneratePayrollRequest;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Services\Payroll\PayrollService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class PayrollController extends Controller
{
    public function __construct(private PayrollService $payrollService) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Payroll::class);

        $year = (int) ($request->year ?? now()->year);
        $month = (int) ($request->month ?? now()->month);
        $user = auth()->user();

        if ($user->can('payroll.generate')) {
            $employees = Employee::with([
                'department',
                'payrolls' => fn ($q) => $q->forMonth($year, $month)->with('items'),
            ])->orderBy('first_name')->get();
        } else {
            $employees = Employee::with([
                'payrolls' => fn ($q) => $q->accessibleBy($user)->orderByDesc('period_year')->orderByDesc('period_month'),
            ])->where('id', $user->employee?->id ?? 0)->get();
        }

        return view('payroll.index', compact('employees', 'year', 'month'));
    }

    public function store(GeneratePayrollRequest $request): RedirectResponse
    {
        $this->authorize('create', Payroll::class);

        $employee = Employee::findOrFail($request->validated()['employee_id']);

        try {
            $payroll = $this->payrollService->generate(
                $employee,
                (int) $request->validated()['year'],
                (int) $request->validated()['month'],
                auth()->user()
            );
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('payroll.show', $payroll)->with('success', 'Payroll generated.');
    }

    public function storeBulk(Request $request): RedirectResponse
    {
        $this->authorize('create', Payroll::class);

        $data = $request->validate([
            'year' => ['required', 'integer', 'min:2020', 'max:2099'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $results = $this->payrollService->generateBulk((int) $data['year'], (int) $data['month'], auth()->user());

        return redirect()
            ->route('payroll.index', ['year' => $data['year'], 'month' => $data['month']])
            ->with('success', "Generated {$results['generated']} payroll(s), skipped {$results['skipped']} (already exist).");
    }

    public function show(Payroll $payroll): View
    {
        $this->authorize('view', $payroll);

        $payroll->load(['employee.department', 'generatedBy', 'items']);

        return view('payroll.show', compact('payroll'));
    }

    public function addItem(AddPayrollItemRequest $request, Payroll $payroll): RedirectResponse
    {
        $this->authorize('addItem', $payroll);

        $data = $request->validated();

        try {
            if ($data['type'] === 'bonus') {
                $this->payrollService->addBonus($payroll, $data['label'], (float) $data['amount']);
            } else {
                $this->payrollService->addDeduction($payroll, $data['label'], (float) $data['amount']);
            }
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', ucfirst($data['type']).' added.');
    }

    public function removeItem(Payroll $payroll, PayrollItem $item): RedirectResponse
    {
        $this->authorize('removeItem', $payroll);

        try {
            $this->payrollService->removeItem($payroll, $item);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Item removed.');
    }

    public function finalize(Payroll $payroll): RedirectResponse
    {
        $this->authorize('finalize', $payroll);

        try {
            $this->payrollService->finalize($payroll, auth()->user());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Payroll finalized and employee notified.');
    }

    public function markPaid(Payroll $payroll): RedirectResponse
    {
        $this->authorize('markPaid', $payroll);

        try {
            $this->payrollService->markPaid($payroll);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Payroll marked as paid.');
    }

    public function downloadPdf(Payroll $payroll): Response
    {
        $this->authorize('downloadPdf', $payroll);

        return $this->payrollService->generatePdf($payroll);
    }
}
