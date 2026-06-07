<?php

namespace App\Http\Controllers;

use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeFile;
use App\Services\Employee\EmployeeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function __construct(private EmployeeService $employeeService) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Employee::class);

        $employees = Employee::with(['department', 'user'])
            ->accessibleBy(auth()->user())
            ->filter($request->only(['search', 'department_id', 'status']))
            ->orderBy('first_name')
            ->paginate(15)
            ->withQueryString();

        $departments = Department::orderBy('name')->get();

        return view('employees.index', compact('employees', 'departments'));
    }

    public function create(): View
    {
        $this->authorize('create', Employee::class);

        $departments = Department::orderBy('name')->get();

        return view('employees.create', compact('departments'));
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $this->authorize('create', Employee::class);

        $employee = $this->employeeService->create(
            $request->validated(),
            $request->file('avatar'),
        );

        return redirect()->route('employees.show', $employee)
            ->with('success', 'Employee created successfully.');
    }

    public function show(Employee $employee): View
    {
        $this->authorize('view', $employee);

        $employee->load(['department', 'user', 'files.uploader']);

        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee): View
    {
        $this->authorize('update', $employee);

        $departments = Department::orderBy('name')->get();

        return view('employees.edit', compact('employee', 'departments'));
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $this->authorize('update', $employee);

        $this->employeeService->update($employee, $request->validated(), $request->file('avatar'));

        return redirect()->route('employees.show', $employee)
            ->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $this->authorize('delete', $employee);

        $this->employeeService->delete($employee);

        return redirect()->route('employees.index')
            ->with('success', 'Employee deleted.');
    }

    public function uploadFile(Request $request, Employee $employee): RedirectResponse
    {
        $this->authorize('uploadFile', $employee);

        $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
            'type' => ['required', Rule::in(['cv', 'contract', 'certificate', 'id_document', 'other'])],
        ]);

        $this->employeeService->storeFile(
            $employee,
            $request->file('file'),
            $request->input('type'),
            auth()->id(),
        );

        return back()->with('success', 'File uploaded successfully.');
    }

    public function destroyFile(Employee $employee, EmployeeFile $file): RedirectResponse
    {
        $this->authorize('uploadFile', $employee);

        abort_if($file->employee_id !== $employee->id, 403);

        $this->employeeService->deleteFile($file);

        return back()->with('success', 'File deleted.');
    }

    public function downloadFile(Employee $employee, EmployeeFile $file)
    {
        $this->authorize('view', $employee);

        abort_if($file->employee_id !== $employee->id, 403);

        return Storage::disk('local')->download($file->file_path, $file->title);
    }
}
