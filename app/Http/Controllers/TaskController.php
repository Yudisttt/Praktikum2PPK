<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Models\Project;
use App\Models\Task;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function create(Workspace $workspace, Project $project): View
    {
        $this->authorize('create', [Task::class, $project]);

        $workspaceMembers = $workspace->members;

        return view('tasks.create', compact('workspace', 'project', 'workspaceMembers'));
    }

    public function store(StoreTaskRequest $request, Workspace $workspace, Project $project): RedirectResponse
    {
        $this->authorize('create', [Task::class, $project]);

        $project->tasks()->create($request->validated());

        return redirect()->route('workspaces.projects.show', [$workspace, $project])
            ->with('success', 'Task berhasil ditambahkan.');
    }

    public function edit(Workspace $workspace, Project $project, Task $task): View
    {
        $this->authorize('update', $task);

        $workspaceMembers = $workspace->members;

        return view('tasks.edit', compact('workspace', 'project', 'task', 'workspaceMembers'));
    }

    public function update(UpdateTaskRequest $request, Workspace $workspace, Project $project, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $task->update($request->validated());

        return redirect()->route('workspaces.projects.show', [$workspace, $project])
            ->with('success', 'Task berhasil diperbarui.');
    }

    public function markDone(Workspace $workspace, Project $project, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $task->update(['status' => TaskStatus::DONE->value]);

        return redirect()->route('workspaces.projects.show', [$workspace, $project])
            ->with('success', 'Task ditandai sebagai Selesai.');
    }

    public function destroy(Workspace $workspace, Project $project, Task $task): RedirectResponse
    {
        $this->authorize('delete', $task);

        $task->delete();

        return redirect()->route('workspaces.projects.show', [$workspace, $project])
            ->with('success', 'Task berhasil dihapus.');
    }
}
