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

test('NFR-3: only user with owner role in workspace_members can delete workspace', function () {
    $owner = User::factory()->create(['status' => true]);
    $member = User::factory()->create(['status' => true]);
    $outsider = User::factory()->create(['status' => true]);

    $workspace = Workspace::create(['name' => 'Secure Workspace', 'owner_id' => $owner->id]);
    $workspace->members()->attach($owner->id, ['role' => WorkspaceRole::OWNER->value]);
    $workspace->members()->attach($member->id, ['role' => WorkspaceRole::MEMBER->value]);

    // Member attempt to delete -> 403 Forbidden
    $memberResponse = $this->actingAs($member)->delete(route('workspaces.destroy', $workspace));
    $memberResponse->assertStatus(403);
    $this->assertDatabaseHas('workspaces', ['id' => $workspace->id]);

    // Outsider attempt to delete -> 403 Forbidden
    $outsiderResponse = $this->actingAs($outsider)->delete(route('workspaces.destroy', $workspace));
    $outsiderResponse->assertStatus(403);
    $this->assertDatabaseHas('workspaces', ['id' => $workspace->id]);

    // Owner attempt to delete -> 302 Redirect & deleted
    $ownerResponse = $this->actingAs($owner)->delete(route('workspaces.destroy', $workspace));
    $ownerResponse->assertRedirect(route('workspaces.index'));
    $this->assertDatabaseMissing('workspaces', ['id' => $workspace->id]);
});

test('NFR-4: workspace records created_at and updated_at timestamps for auditability', function () {
    $user = User::factory()->create(['status' => true]);

    $this->actingAs($user)->post(route('workspaces.store'), [
        'name' => 'Audited Workspace',
    ]);

    $workspace = Workspace::where('name', 'Audited Workspace')->first();
    expect($workspace)->not->toBeNull();
    expect($workspace->created_at)->not->toBeNull();
    expect($workspace->updated_at)->not->toBeNull();
});

test('NFR-5: workspace show page contains explicit confirmation on delete action', function () {
    $owner = User::factory()->create(['status' => true]);
    $workspace = Workspace::create(['name' => 'UI Confirmation Workspace', 'owner_id' => $owner->id]);
    $workspace->members()->attach($owner->id, ['role' => WorkspaceRole::OWNER->value]);

    $response = $this->actingAs($owner)->get(route('workspaces.show', $workspace));

    $response->assertOk();
    $response->assertSee('onsubmit="return confirm(', false);
});
