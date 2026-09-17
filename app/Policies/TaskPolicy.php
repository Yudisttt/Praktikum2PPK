<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function view(User $user, Task $task): bool
    {
        return $task->project->workspace->members()->where('user_id', $user->id)->exists();
    }

    public function create(User $user, Project $project): bool
    {
        return $project->workspace->members()->where('user_id', $user->id)->exists();
    }

    public function update(User $user, Task $task): bool
    {
        return $task->project->workspace->members()->where('user_id', $user->id)->exists();
    }

    public function delete(User $user, Task $task): bool
    {
        return $task->project->workspace->members()->where('user_id', $user->id)->exists();
    }
}
