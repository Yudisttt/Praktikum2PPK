<?php

namespace App\Http\Controllers;

use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Models\Project;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function create(Workspace $workspace): View
    {
        $this->authorize('create', [Project::class, $workspace]);

        return view('projects.create', compact('workspace'));
    }

    public function store(StoreProjectRequest $request, Workspace $workspace): RedirectResponse
    {
        $this->authorize('create', [Project::class, $workspace]);

        $project = $workspace->projects()->create($request->validated());

        return redirect()->route('workspaces.projects.show', [$workspace, $project])
            ->with('success', 'Project berhasil dibuat.');
    }

    public function show(Workspace $workspace, Project $project): View
    {
        $this->authorize('view', $project);

        $project->load(['tasks.assignee', 'workspace.members']);

        return view('projects.show', compact('workspace', 'project'));
    }

    public function edit(Workspace $workspace, Project $project): View
    {
        $this->authorize('update', $project);

        return view('projects.edit', compact('workspace', 'project'));
    }

    public function update(UpdateProjectRequest $request, Workspace $workspace, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $project->update($request->validated());

        return redirect()->route('workspaces.projects.show', [$workspace, $project])
            ->with('success', 'Project berhasil diperbarui.');
    }

    public function destroy(Workspace $workspace, Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        $project->delete();

        return redirect()->route('workspaces.show', $workspace)
            ->with('success', 'Project berhasil dihapus.');
    }
}
