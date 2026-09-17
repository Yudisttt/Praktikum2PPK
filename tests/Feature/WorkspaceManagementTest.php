<?php

use App\Enums\WorkspaceRole;
use App\Models\User;
use App\Models\Workspace;

test('user can create workspace and becomes owner in workspace_members', function () {
    $user = User::factory()->create(['status' => true]);

    $response = $this->actingAs($user)->post(route('workspaces.store'), [
        'name' => 'Workspace Alpha',
    ]);

    $workspace = Workspace::where('name', 'Workspace Alpha')->first();
    expect($workspace)->not->toBeNull();
    $response->assertRedirect(route('workspaces.show', $workspace));

    $this->assertDatabaseHas('workspace_members', [
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
        'role' => WorkspaceRole::OWNER->value,
    ]);
});

test('workspace owner can invite new member by email', function () {
    $owner = User::factory()->create(['status' => true]);
    $memberUser = User::factory()->create(['status' => true]);

    $workspace = Workspace::create(['name' => 'Team Workspace', 'owner_id' => $owner->id]);
    $workspace->members()->attach($owner->id, ['role' => WorkspaceRole::OWNER->value]);

    $response = $this->actingAs($owner)->post(route('workspaces.members.invite', $workspace), [
        'email' => $memberUser->email,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('workspace_members', [
        'workspace_id' => $workspace->id,
        'user_id' => $memberUser->id,
        'role' => WorkspaceRole::MEMBER->value,
    ]);
});

test('member cannot invite or remove members from workspace', function () {
    $owner = User::factory()->create(['status' => true]);
    $member = User::factory()->create(['status' => true]);
    $targetUser = User::factory()->create(['status' => true]);

    $workspace = Workspace::create(['name' => 'Team Workspace', 'owner_id' => $owner->id]);
    $workspace->members()->attach($owner->id, ['role' => WorkspaceRole::OWNER->value]);
    $workspace->members()->attach($member->id, ['role' => WorkspaceRole::MEMBER->value]);

    $response = $this->actingAs($member)->post(route('workspaces.members.invite', $workspace), [
        'email' => $targetUser->email,
    ]);

    $response->assertStatus(403);
});
