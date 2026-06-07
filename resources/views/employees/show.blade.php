<x-app-layout>
    @section('breadcrumb')
        <x-breadcrumb :items="[['label' => 'Employees', 'href' => route('employees.index')], ['label' => $employee->full_name]]" />
    @endsection

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('employees.index') }}" class="text-gray-400 hover:text-gray-600">Employees</a>
                <span class="text-gray-300">/</span>
                <h2 class="text-xl font-semibold text-gray-800">{{ $employee->full_name }}</h2>
            </div>
            @can('employees.update', $employee)
                <a href="{{ route('employees.edit', $employee) }}"
                   class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                    Edit
                </a>
            @endcan
        </div>
    </x-slot>

    @if(session('success'))
        <div class="mb-4 rounded-md bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif

    <div x-data="{ tab: 'details' }" class="space-y-6">

        <div class="rounded-lg bg-white shadow">
            <div class="flex items-center gap-6 p-6">
                <div class="flex-shrink-0">
                    <div class="h-20 w-20 rounded-full bg-indigo-100 flex items-center justify-center text-2xl font-semibold text-indigo-600">
                        {{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) }}
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xl font-semibold text-gray-900">{{ $employee->full_name }}</p>
                    <p class="text-sm text-gray-500">{{ $employee->job_title }}</p>
                    <p class="text-sm text-gray-400 mt-1">{{ $employee->employee_code }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    @php
                        $badge = match($employee->employment_status) {
                            'active'     => 'bg-green-100 text-green-800',
                            'probation'  => 'bg-yellow-100 text-yellow-800',
                            'terminated' => 'bg-red-100 text-red-800',
                        };
                    @endphp
                    <span class="rounded-full px-3 py-1 text-xs font-medium {{ $badge }}">
                        {{ ucfirst($employee->employment_status) }}
                    </span>
                    @if($employee->user)
                        <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700">
                            {{ $employee->user->getRoleNames()->first() }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="border-t border-gray-200">
                <nav class="flex">
                    <button @click="tab = 'details'"
                            :class="tab === 'details' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                            class="border-b-2 px-6 py-4 text-sm font-medium">
                        Details
                    </button>
                    <button @click="tab = 'files'"
                            :class="tab === 'files' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                            class="border-b-2 px-6 py-4 text-sm font-medium">
                        Files ({{ $employee->files->count() }})
                    </button>
                </nav>
            </div>
        </div>

        <div x-show="tab === 'details'" class="rounded-lg bg-white shadow">
            <dl class="grid grid-cols-1 sm:grid-cols-2 divide-y sm:divide-y-0 sm:divide-x divide-gray-100">
                @php
                    $row = fn($label, $value) => [$label, $value ?? '—'];
                    $rows = [
                        $row('Department',    $employee->department?->name),
                        $row('Hire Date',     $employee->hire_date->format('d M Y')),
                        $row('Phone',         $employee->phone),
                        $row('Gender',        $employee->gender ? ucfirst($employee->gender) : null),
                        $row('Date of Birth', $employee->date_of_birth?->format('d M Y')),
                        $row('Base Salary',   number_format($employee->base_salary, 2)),
                        $row('Login Email',   $employee->user?->email),
                        $row('Address',       $employee->address),
                    ];
                @endphp
                @foreach($rows as [$label, $value])
                    <div class="px-6 py-4">
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ $label }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>

        <div x-show="tab === 'files'" class="space-y-4">
            @can('uploadFile', $employee)
            <div class="rounded-lg bg-white p-6 shadow">
                <h3 class="mb-4 text-sm font-semibold text-gray-700">Upload File</h3>
                <form method="POST" action="{{ route('employees.files.store', $employee) }}"
                      enctype="multipart/form-data" class="flex flex-wrap items-end gap-3">
                    @csrf
                    <div>
                        <x-input-label for="type" value="Type" />
                        <select id="type" name="type"
                                class="mt-1 block rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="cv">CV</option>
                            <option value="contract">Contract</option>
                            <option value="certificate">Certificate</option>
                            <option value="id_document">ID Document</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label for="file" value="File (max 10 MB)" />
                        <input id="file" name="file" type="file"
                               class="mt-1 block text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100" />
                    </div>
                    <x-primary-button>Upload</x-primary-button>
                </form>
                @if($errors->has('file') || $errors->has('type'))
                    <div class="mt-2 text-sm text-red-600">
                        <x-input-error :messages="$errors->get('file')" />
                        <x-input-error :messages="$errors->get('type')" />
                    </div>
                @endif
            </div>
            @endcan

            <div class="rounded-lg bg-white shadow overflow-hidden">
                @forelse($employee->files as $file)
                    <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 last:border-0">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $file->title }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ strtoupper($file->type) }} &middot; {{ $file->formatted_size }}
                                &middot; Uploaded by {{ $file->uploader->name }}
                                on {{ $file->created_at->format('d M Y') }}
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('employees.files.download', [$employee, $file]) }}"
                               class="text-sm text-indigo-600 hover:text-indigo-900">Download</a>
                            @can('uploadFile', $employee)
                                <span x-data>
                                    <form method="POST" action="{{ route('employees.files.destroy', [$employee, $file]) }}" x-ref="deleteForm" class="hidden">
                                        @csrf @method('DELETE')
                                    </form>
                                    <button type="button" class="text-sm text-red-600 hover:text-red-900"
                                            @click="$dispatch('confirm', { title: 'Delete File', message: 'This file will be permanently removed.', onConfirm: () => $refs.deleteForm.submit() })">
                                        Delete
                                    </button>
                                </span>
                            @endcan
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-10 text-center text-sm text-gray-400">No files uploaded yet.</div>
                @endforelse
            </div>
        </div>

    </div>
</x-app-layout>
