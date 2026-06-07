<?php

namespace App\Http\Controllers;

use App\Http\Requests\Task\ReassignTaskRequest;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\TaskCommentRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Models\Employee;
use App\Models\Task;
use App\Models\User;
use App\Services\Task\TaskService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class TaskController extends Controller
{
    public function __construct(private TaskService $taskService) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Task::class);

        $user = auth()->user();

        $tasks = Task::accessibleBy($user)
            ->filter($request->only(['status', 'priority', 'assigned_to', 'search']))
            ->with(['assignee', 'assigner'])
            ->latest()
            ->get();

        $columns = collect(Task::STATUSES)->mapWithKeys(
            fn ($status) => [$status => $tasks->where('status', $status)->values()]
        );

        return view('tasks.index', [
            'columns' => $columns,
            'statuses' => Task::STATUSES,
            'filters' => $request->only(['status', 'priority', 'assigned_to', 'search']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Task::class);

        return view('tasks.create', [
            'assignees' => $this->assignableUsers(auth()->user()),
        ]);
    }

    public function store(StoreTaskRequest $request): RedirectResponse
    {
        $this->authorize('create', Task::class);

        $task = $this->taskService->create($request->validated(), auth()->user());

        return redirect()->route('tasks.show', $task)->with('success', 'Task created and assigned.');
    }

    public function show(Task $task): View
    {
        $this->authorize('view', $task);

        $task->load([
            'assignee', 'assigner', 'reviewer',
            'comments.user',
            'histories.user',
        ]);

        return view('tasks.show', [
            'task' => $task,
            'assignees' => auth()->user()->can('reassign', $task)
                ? $this->assignableUsers(auth()->user())
                : new Collection,
        ]);
    }

    public function edit(Task $task): View
    {
        $this->authorize('update', $task);

        return view('tasks.edit', ['task' => $task]);
    }

    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $this->taskService->update($task, $request->validated());

        return redirect()->route('tasks.show', $task)->with('success', 'Task updated.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $this->authorize('delete', $task);

        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Task deleted.');
    }

    public function start(Task $task): RedirectResponse
    {
        $this->authorize('start', $task);

        return $this->transition($task, 'in_progress', 'Task started.');
    }

    public function submit(Task $task): RedirectResponse
    {
        $this->authorize('submit', $task);

        return $this->transition($task, 'submitted', 'Task submitted for review.');
    }

    public function approve(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('review', $task);

        $notes = $request->validate(['review_notes' => ['nullable', 'string', 'max:2000']])['review_notes'] ?? null;

        return $this->transition($task, 'approved', 'Task approved.', $notes);
    }

    public function reject(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('review', $task);

        $notes = $request->validate(['review_notes' => ['nullable', 'string', 'max:2000']])['review_notes'] ?? null;

        return $this->transition($task, 'rejected', 'Task rejected.', $notes);
    }

    public function reassign(ReassignTaskRequest $request, Task $task): RedirectResponse
    {
        $this->authorize('reassign', $task);

        try {
            $this->taskService->reassign($task, (int) $request->validated()['assigned_to'], auth()->user());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Task reassigned.');
    }

    public function comment(TaskCommentRequest $request, Task $task): RedirectResponse
    {
        $this->authorize('comment', $task);

        $this->taskService->addComment($task, auth()->user(), $request->validated()['body']);

        return back()->with('success', 'Comment added.');
    }

    private function transition(Task $task, string $to, string $message, ?string $notes = null): RedirectResponse
    {
        try {
            $this->taskService->changeStatus($task, $to, auth()->user(), $notes);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $message);
    }

    private function assignableUsers(User $actor): Collection
    {
        $query = User::whereHas('employee')->orderBy('name');

        if ($actor->hasRole('Team Lead') && ! $actor->hasRole(['Admin', 'HR Manager'])) {
            $deptId = $actor->employee?->department_id;
            $deptUserIds = Employee::where('department_id', $deptId)
                ->whereNotNull('user_id')
                ->pluck('user_id');

            $query->whereIn('id', $deptUserIds);
        }

        return $query->get();
    }
}
