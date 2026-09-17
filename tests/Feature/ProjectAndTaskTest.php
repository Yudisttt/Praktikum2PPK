<?php

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Enums\WorkspaceRole;
use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;

test('workspace member can create project and task', function () {
    $user = User::factory()->create(['status' => true]);
    $workspace = Workspace::create(['name' => 'Dev Studio', 'owner_id' => $user->id]);
    $workspace->members()->attach($user->id, ['role' => WorkspaceRole::OWNER->value]);

    $projectResponse = $this->actingAs($user)->post(route('workspaces.projects.store', $workspace), [
        'name' => 'Web Redesign',
        'description' => 'Redesigning company web',
    ]);

    $project = Project::where('name', 'Web Redesign')->first();
    expect($project)->not->toBeNull();

    $taskResponse = $this->actingAs($user)->post(route('workspaces.projects.tasks.store', [$workspace, $project]), [
        'title' => 'Navbar Component',
        'priority' => TaskPriority::HIGH->value,
        'status' => TaskStatus::TODO->value,
        'assigned_to' => $user->id,
    ]);

    $taskResponse->assertRedirect(route('workspaces.projects.show', [$workspace, $project]));
    $this->assertDatabaseHas('tasks', [
        'project_id' => $project->id,
        'title' => 'Navbar Component',
        'assigned_to' => $user->id,
    ]);
});

test('cannot assign task to a user outside the workspace', function () {
    $user = User::factory()->create(['status' => true]);
    $outsider = User::factory()->create(['status' => true]);

    $workspace = Workspace::create(['name' => 'Dev Studio', 'owner_id' => $user->id]);
    $workspace->members()->attach($user->id, ['role' => WorkspaceRole::OWNER->value]);
    $project = $workspace->projects()->create(['name' => 'Web Redesign']);

    $response = $this->actingAs($user)->post(route('workspaces.projects.tasks.store', [$workspace, $project]), [
        'title' => 'Invalid Assignment Task',
        'priority' => TaskPriority::MEDIUM->value,
        'status' => TaskStatus::TODO->value,
        'assigned_to' => $outsider->id,
    ]);

    $response->assertSessionHasErrors('assigned_to');
});

test('task status update dynamically recalculates project progress percentage', function () {
    $user = User::factory()->create(['status' => true]);
    $workspace = Workspace::create(['name' => 'Dev Studio', 'owner_id' => $user->id]);
    $workspace->members()->attach($user->id, ['role' => WorkspaceRole::OWNER->value]);
    $project = $workspace->projects()->create(['name' => 'Mobile App']);

    $task1 = $project->tasks()->create(['title' => 'Task 1', 'status' => TaskStatus::TODO->value]);
    $task2 = $project->tasks()->create(['title' => 'Task 2', 'status' => TaskStatus::TODO->value]);

    expect($project->fresh()->progressPercentage())->toBe(0);

    $this->actingAs($user)->patch(route('workspaces.projects.tasks.markDone', [$workspace, $project, $task1]));

    expect($project->fresh()->progressPercentage())->toBe(50);
});
