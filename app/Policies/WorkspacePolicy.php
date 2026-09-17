<?php

namespace App\Policies;

use App\Enums\WorkspaceRole;
use App\Models\User;
use App\Models\Workspace;

class WorkspacePolicy
{
    /**
     * Check if user is a member or owner of the workspace.
     */
    public function view(User $user, Workspace $workspace): bool
    {
        return $workspace->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if user has 'owner' role in workspace_members table.
     */
    public function update(User $user, Workspace $workspace): bool
    {
        return $this->isWorkspaceOwner($user, $workspace);
    }

    /**
     * Check if user has 'owner' role in workspace_members table.
     */
    public function delete(User $user, Workspace $workspace): bool
    {
        return $this->isWorkspaceOwner($user, $workspace);
    }

    /**
     * Check if user has 'owner' role to invite members.
     */
    public function inviteMember(User $user, Workspace $workspace): bool
    {
        return $this->isWorkspaceOwner($user, $workspace);
    }

    /**
     * Check if user has 'owner' role to remove members.
     */
    public function removeMember(User $user, Workspace $workspace): bool
    {
        return $this->isWorkspaceOwner($user, $workspace);
    }

    private function isWorkspaceOwner(User $user, Workspace $workspace): bool
    {
        return $workspace->workspaceMembers()
            ->where('user_id', $user->id)
            ->where('role', WorkspaceRole::OWNER->value)
            ->exists();
    }
}
