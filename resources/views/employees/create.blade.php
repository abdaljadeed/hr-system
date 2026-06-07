<x-app-layout>
    @section('breadcrumb')
        <x-breadcrumb :items="[['label' => 'Employees', 'href' => route('employees.index')], ['label' => 'New Employee']]" />
    @endsection

    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('employees.index') }}" class="text-gray-400 hover:text-gray-600">Employees</a>
            <span class="text-gray-300">/</span>
            <h2 class="text-xl font-semibold text-gray-800">New Employee</h2>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('employees.store') }}" enctype="multipart/form-data"
          class="space-y-6">
        @csrf

        <div class="rounded-lg bg-white p-6 shadow space-y-6">
            <h3 class="text-base font-semibold text-gray-900 border-b border-gray-200 pb-3">Personal Information</h3>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="first_name" value="First Name *" />
                    <x-text-input id="first_name" name="first_name" type="text" class="mt-1 block w-full"
                                  value="{{ old('first_name') }}" required />
                    <x-input-error :messages="$errors->get('first_name')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="last_name" value="Last Name *" />
                    <x-text-input id="last_name" name="last_name" type="text" class="mt-1 block w-full"
                                  value="{{ old('last_name') }}" required />
                    <x-input-error :messages="$errors->get('last_name')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="phone" value="Phone" />
                    <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full"
                                  value="{{ old('phone') }}" />
                    <x-input-error :messages="$errors->get('phone')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="gender" value="Gender" />
                    <select id="gender" name="gender"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="">— Select —</option>
                        <option value="male"   @selected(old('gender') === 'male')>Male</option>
                        <option value="female" @selected(old('gender') === 'female')>Female</option>
                    </select>
                    <x-input-error :messages="$errors->get('gender')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="date_of_birth" value="Date of Birth" />
                    <x-text-input id="date_of_birth" name="date_of_birth" type="date" class="mt-1 block w-full"
                                  value="{{ old('date_of_birth') }}" />
                    <x-input-error :messages="$errors->get('date_of_birth')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="avatar" value="Profile Photo" />
                    <input id="avatar" name="avatar" type="file" accept="image/*"
                           class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100" />
                    <x-input-error :messages="$errors->get('avatar')" class="mt-1" />
                </div>
                <div class="sm:col-span-2">
                    <x-input-label for="address" value="Address" />
                    <textarea id="address" name="address" rows="2"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old('address') }}</textarea>
                    <x-input-error :messages="$errors->get('address')" class="mt-1" />
                </div>
            </div>
        </div>

        <div class="rounded-lg bg-white p-6 shadow space-y-6">
            <h3 class="text-base font-semibold text-gray-900 border-b border-gray-200 pb-3">Employment Details</h3>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="employee_code" value="Employee Code" />
                    <x-text-input id="employee_code" name="employee_code" type="text" class="mt-1 block w-full"
                                  value="{{ old('employee_code') }}" placeholder="Auto-generated if blank" />
                    <x-input-error :messages="$errors->get('employee_code')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="department_id" value="Department" />
                    <select id="department_id" name="department_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="">— No department —</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" @selected(old('department_id') == $dept->id)>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('department_id')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="job_title" value="Job Title *" />
                    <x-text-input id="job_title" name="job_title" type="text" class="mt-1 block w-full"
                                  value="{{ old('job_title') }}" required />
                    <x-input-error :messages="$errors->get('job_title')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="hire_date" value="Hire Date *" />
                    <x-text-input id="hire_date" name="hire_date" type="date" class="mt-1 block w-full"
                                  value="{{ old('hire_date') }}" required />
                    <x-input-error :messages="$errors->get('hire_date')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="employment_status" value="Status *" />
                    <select id="employment_status" name="employment_status"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" required>
                        <option value="active"     @selected(old('employment_status','active') === 'active')>Active</option>
                        <option value="probation"  @selected(old('employment_status') === 'probation')>Probation</option>
                        <option value="terminated" @selected(old('employment_status') === 'terminated')>Terminated</option>
                    </select>
                    <x-input-error :messages="$errors->get('employment_status')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="base_salary" value="Base Salary *" />
                    <x-text-input id="base_salary" name="base_salary" type="number" step="0.01" min="0"
                                  class="mt-1 block w-full" value="{{ old('base_salary', '0.00') }}" required />
                    <x-input-error :messages="$errors->get('base_salary')" class="mt-1" />
                </div>
            </div>
        </div>

        @can('users.manage')
        <div x-data="{ provision: {{ old('provision_user') ? 'true' : 'false' }} }"
             class="rounded-lg bg-white p-6 shadow space-y-4">
            <div class="flex items-center gap-3 border-b border-gray-200 pb-3">
                <input type="hidden" name="provision_user" value="0" />
                <input id="provision_user" name="provision_user" type="checkbox" value="1"
                       x-model="provision" @change="provision = $event.target.checked"
                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                       {{ old('provision_user') ? 'checked' : '' }} />
                <x-input-label for="provision_user" value="Provision a login account for this employee" class="mb-0" />
            </div>

            <div x-show="provision" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="email" value="Email" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                                  value="{{ old('email') }}" />
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="role" value="Role" />
                    <select id="role" name="role"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="Employee"    @selected(old('role','Employee') === 'Employee')>Employee</option>
                        <option value="Team Lead"   @selected(old('role') === 'Team Lead')>Team Lead</option>
                        <option value="HR Manager"  @selected(old('role') === 'HR Manager')>HR Manager</option>
                    </select>
                    <x-input-error :messages="$errors->get('role')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="password" value="Password" />
                    <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="password_confirmation" value="Confirm Password" />
                    <x-text-input id="password_confirmation" name="password_confirmation" type="password"
                                  class="mt-1 block w-full" />
                </div>
            </div>
        </div>
        @endcan

        <div class="flex items-center gap-4">
            <x-primary-button>Create Employee</x-primary-button>
            <a href="{{ route('employees.index') }}"
               class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
        </div>
    </form>
</x-app-layout>
