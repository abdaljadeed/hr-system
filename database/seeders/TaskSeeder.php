<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\Task\TaskService;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function __construct(private TaskService $taskService) {}

    public function run(): void
    {
        $lead = User::where('email', 'lead@hr.test')->first();
        $employee = User::where('email', 'employee@hr.test')->first();

        if (! $lead || ! $employee) {
            return;
        }

        $todo = $this->taskService->create([
            'title' => 'Set up CI pipeline',
            'description' => 'Configure GitHub Actions to run the test suite on every pull request.',
            'assigned_to' => $employee->id,
            'priority' => 'high',
            'due_date' => now()->addDays(5)->toDateString(),
        ], $lead);

        $inProgress = $this->taskService->create([
            'title' => 'Refactor authentication module',
            'description' => 'Extract the auth logic into a dedicated service class.',
            'assigned_to' => $employee->id,
            'priority' => 'medium',
            'due_date' => now()->addDays(10)->toDateString(),
        ], $lead);

        $this->taskService->changeStatus($inProgress, 'in_progress', $employee);
        $this->taskService->addComment($inProgress, $employee, 'Started working on the service extraction.');

        $submitted = $this->taskService->create([
            'title' => 'Write API documentation',
            'description' => 'Document all public endpoints with request/response examples.',
            'assigned_to' => $employee->id,
            'priority' => 'low',
            'due_date' => now()->subDays(2)->toDateString(),
        ], $lead);

        $this->taskService->changeStatus($submitted, 'in_progress', $employee);
        $this->taskService->changeStatus($submitted, 'submitted', $employee);

        $approved = $this->taskService->create([
            'title' => 'Fix login redirect bug',
            'description' => 'Users are not redirected to the dashboard after login.',
            'assigned_to' => $employee->id,
            'priority' => 'high',
        ], $lead);

        $this->taskService->changeStatus($approved, 'in_progress', $employee);
        $this->taskService->changeStatus($approved, 'submitted', $employee);
        $this->taskService->changeStatus($approved, 'approved', $lead, 'Great work, verified locally.');
    }
}
