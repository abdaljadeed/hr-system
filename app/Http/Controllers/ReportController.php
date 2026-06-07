<?php

namespace App\Http\Controllers;

use App\Exports\AttendanceReportExport;
use App\Exports\EmployeeListExport;
use App\Exports\LeaveReportExport;
use App\Exports\PayrollReportExport;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\User;
use App\Services\Report\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function __construct(private ReportService $reportService) {}

    public function index(): View
    {
        $user = auth()->user();

        return view('reports.index', [
            'employees' => Employee::accessibleBy($user)->orderBy('first_name')->get(),
            'departments' => Department::orderBy('name')->get(),
            'leaveTypes' => LeaveType::orderBy('name')->get(),
            'years' => range((int) now()->year, (int) now()->year - 3),
        ]);
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $user = auth()->user();
        $filters = $request->all();

        [$export, $filename] = match ($request->input('report_type')) {
            'attendance' => [new AttendanceReportExport($this->reportService->attendance($user, $filters)), 'attendance-'.$this->periodSlug($filters).'.xlsx'],
            'payroll' => [new PayrollReportExport($this->reportService->payroll($user, $filters)), 'payroll-'.$this->periodSlug($filters).'.xlsx'],
            'employees' => [new EmployeeListExport($this->reportService->employees($user, $filters)), 'employees.xlsx'],
            'leaves' => [new LeaveReportExport($this->reportService->leaves($user, $filters)), 'leaves.xlsx'],
            default => abort(404),
        };

        return Excel::download($export, $filename);
    }

    public function exportPdf(Request $request): Response
    {
        $payload = $this->pdfPayload($request->input('report_type'), $request->all(), auth()->user());

        return Pdf::loadView($payload['view'], $payload['data'])
            ->setPaper('a4', $payload['orientation'])
            ->download($payload['filename']);
    }

    public function preview(Request $request): View
    {
        $payload = $this->pdfPayload($request->input('report_type'), $request->all(), auth()->user());

        return view($payload['view'], $payload['data']);
    }

    private function pdfPayload(string $type, array $filters, User $user): array
    {
        return match ($type) {
            'attendance' => $this->attendancePayload($user, $filters),
            'payroll' => $this->payrollPayload($user, $filters),
            'performance' => $this->performancePayload($user, $filters),
            default => abort(404),
        };
    }

    private function attendancePayload(User $user, array $filters): array
    {
        $rows = $this->reportService->attendance($user, $filters);

        $totals = [
            'present' => $rows->where('status', 'present')->count(),
            'late' => $rows->where('status', 'late')->count(),
            'absent' => $rows->where('status', 'absent')->count(),
            'on_leave' => $rows->where('status', 'on_leave')->count(),
            'worked_hours' => (float) $rows->sum('worked_hours'),
        ];

        return [
            'view' => 'reports.attendance-pdf',
            'orientation' => 'landscape',
            'filename' => 'attendance-'.$this->periodSlug($filters).'.pdf',
            'data' => [
                'rows' => $rows,
                'totals' => $totals,
                'period' => $this->periodLabel($filters),
                'filterSummary' => $this->employeeSummary($filters),
            ],
        ];
    }

    private function payrollPayload(User $user, array $filters): array
    {
        $rows = $this->reportService->payroll($user, $filters);

        return [
            'view' => 'reports.payroll-pdf',
            'orientation' => 'landscape',
            'filename' => 'payroll-'.$this->periodSlug($filters).'.pdf',
            'data' => [
                'rows' => $rows,
                'totalCost' => (float) $rows->sum('net_salary'),
                'period' => $this->periodLabel($filters),
                'filterSummary' => 'All Employees',
            ],
        ];
    }

    private function performancePayload(User $user, array $filters): array
    {
        abort_if(empty($filters['employee_id']), 404);

        $data = $this->reportService->performance($user, $filters);
        $data['period'] = $this->periodLabel($filters);

        return [
            'view' => 'reports.employee-performance-pdf',
            'orientation' => 'portrait',
            'filename' => 'performance-'.$data['employee']->employee_code.'-'.$this->periodSlug($filters).'.pdf',
            'data' => $data,
        ];
    }

    private function employeeSummary(array $filters): string
    {
        if (! empty($filters['employee_id'])) {
            return Employee::find($filters['employee_id'])?->full_name ?? 'Unknown';
        }

        return 'All Employees';
    }

    private function periodLabel(array $filters): string
    {
        return Carbon::create((int) ($filters['year'] ?? now()->year), (int) ($filters['month'] ?? now()->month), 1)->format('F Y');
    }

    private function periodSlug(array $filters): string
    {
        return sprintf('%d-%02d', (int) ($filters['year'] ?? now()->year), (int) ($filters['month'] ?? now()->month));
    }
}
