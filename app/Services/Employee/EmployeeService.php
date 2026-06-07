<?php

namespace App\Services\Employee;

use App\Models\Employee;
use App\Models\EmployeeFile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class EmployeeService
{
    public function create(array $data, ?UploadedFile $avatar = null): Employee
    {
        return DB::transaction(function () use ($data, $avatar) {
            if (! empty($data['provision_user'])) {
                $user = User::create([
                    'name' => "{$data['first_name']} {$data['last_name']}",
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]);
                $user->assignRole($data['role'] ?? 'Employee');
                $data['user_id'] = $user->id;
            }

            $data['employee_code'] = $data['employee_code'] ?: Employee::generateCode();

            if ($avatar) {
                $data['avatar_path'] = $avatar->store('avatars', 'local');
            }

            $employee = Employee::create($this->employeeFields($data));

            activity()->causedBy(auth()->user())->performedOn($employee)
                ->log("Created employee {$employee->full_name}");

            return $employee;
        });
    }

    public function update(Employee $employee, array $data, ?UploadedFile $avatar = null): Employee
    {
        return DB::transaction(function () use ($employee, $data, $avatar) {
            if ($avatar) {
                if ($employee->avatar_path) {
                    Storage::disk('local')->delete($employee->avatar_path);
                }
                $data['avatar_path'] = $avatar->store('avatars', 'local');
            }

            $employee->update($this->employeeFields($data));
            $employee = $employee->fresh();

            activity()->causedBy(auth()->user())->performedOn($employee)
                ->log("Updated employee {$employee->full_name}");

            return $employee;
        });
    }

    public function delete(Employee $employee): void
    {
        $name = $employee->full_name;

        activity()->causedBy(auth()->user())->performedOn($employee)
            ->log("Deleted employee {$name}");

        $employee->delete();
    }

    public function storeFile(Employee $employee, UploadedFile $file, string $type, int $uploadedBy): EmployeeFile
    {
        $path = $file->store("employee-files/{$employee->id}", 'local');

        return $employee->files()->create([
            'type' => $type,
            'title' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'uploaded_by' => $uploadedBy,
        ]);
    }

    public function deleteFile(EmployeeFile $file): void
    {
        Storage::disk('local')->delete($file->file_path);
        $file->delete();
    }

    private function employeeFields(array $data): array
    {
        return array_intersect_key($data, array_flip([
            'user_id', 'department_id', 'employee_code', 'first_name', 'last_name',
            'phone', 'gender', 'date_of_birth', 'job_title', 'hire_date',
            'employment_status', 'base_salary', 'address', 'avatar_path',
        ]));
    }
}
