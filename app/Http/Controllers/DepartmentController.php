<?php

namespace App\Http\Controllers;

use App\Http\Requests\Department\StoreDepartmentRequest;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Department::class);

        $departments = Department::with(['manager', 'employees'])
            ->search($request->input('search'))
            ->withCount('employees')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('departments.index', compact('departments'));
    }

    public function create(): View
    {
        $this->authorize('create', Department::class);

        $employees = Employee::orderBy('first_name')->get();

        return view('departments.create', compact('employees'));
    }

    public function store(StoreDepartmentRequest $request): RedirectResponse
    {
        $this->authorize('create', Department::class);

        Department::create($request->validated());

        return redirect()->route('departments.index')
            ->with('success', 'Department created successfully.');
    }

    public function edit(Department $department): View
    {
        $this->authorize('update', $department);

        $employees = Employee::orderBy('first_name')->get();

        return view('departments.edit', compact('department', 'employees'));
    }

    public function update(StoreDepartmentRequest $request, Department $department): RedirectResponse
    {
        $this->authorize('update', $department);

        $department->update($request->validated());

        return redirect()->route('departments.index')
            ->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        $this->authorize('delete', $department);

        $department->delete();

        return redirect()->route('departments.index')
            ->with('success', 'Department deleted.');
    }
}
