<?php

use App\Enums\WorkspaceRole;
use App\Models\User;
use App\Models\Workspace;

// FR-1 & FR-2: Logged-in user can create workspace & automatically becomes owner in workspace_members
test('user can create workspace and automatically becomes owner in workspace_members (FR-1 & FR-2)', function () {
    $user = User::factory()->create(['status' => true]);

    $response = $this->actingAs($user)->post(route('workspaces.store'), [
        'name' => 'Workspace Alpha',
    ]);

    $workspace = Workspace::where('name', 'Workspace Alpha')->first();
    expect($workspace)->not->toBeNull();
    $response->assertRedirect(route('workspaces.show', $workspace));
    $response->assertSessionHas('success', 'Workspace berhasil dibuat.');

    $this->assertDatabaseHas('workspace_members', [
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
        'role' => WorkspaceRole::OWNER->value,
    ]);
});

// FR-3: Creation must be atomic (no partial write if member attachment fails)
test('workspace creation is atomic and rolls back on failure with no partial write (FR-3 & FR-8)', function () {
    $user = User::factory()->create(['status' => true]);

    Workspace::created(function ($ws) {
        if ($ws->name === 'Atomic Rollback Workspace') {
            throw new Exception('Simulated failure during member attachment');
        }
    });

    $response = $this->actingAs($user)->post(route('workspaces.store'), [
        'name' => 'Atomic Rollback Workspace',
    ]);

    $response->assertSessionHas('error');
    // Ensure no partial write occurred in workspaces table (FR-3)
    $this->assertDatabaseMissing('workspaces', ['name' => 'Atomic Rollback Workspace']);
});

// FR-4 & FR-5: Owner can delete workspace and its members atomically
test('workspace owner can delete workspace and its members atomically (FR-4 & FR-5)', function () {
    $owner = User::factory()->create(['status' => true]);
    $member = User::factory()->create(['status' => true]);

    $workspace = Workspace::create(['name' => 'Workspace to Delete', 'owner_id' => $owner->id]);
    $workspace->members()->attach($owner->id, ['role' => WorkspaceRole::OWNER->value]);
    $workspace->members()->attach($member->id, ['role' => WorkspaceRole::MEMBER->value]);

    $response = $this->actingAs($owner)->delete(route('workspaces.destroy', $workspace));

    $response->assertRedirect(route('workspaces.index'));
    $response->assertSessionHas('success', 'Workspace berhasil dihapus.');

    $this->assertDatabaseMissing('workspaces', ['id' => $workspace->id]);
    $this->assertDatabaseMissing('workspace_members', ['workspace_id' => $workspace->id]);
});

// FR-6: Non-owner cannot delete workspace and receives 403 Forbidden
test('non-owner cannot delete workspace and receives 403 forbidden (FR-6)', function () {
    $owner = User::factory()->create(['status' => true]);
    $member = User::factory()->create(['status' => true]);
    $stranger = User::factory()->create(['status' => true]);

    $workspace = Workspace::create(['name' => 'Protected Workspace', 'owner_id' => $owner->id]);
    $workspace->members()->attach($owner->id, ['role' => WorkspaceRole::OWNER->value]);
    $workspace->members()->attach($member->id, ['role' => WorkspaceRole::MEMBER->value]);

    // Member attempts delete
    $responseMember = $this->actingAs($member)->delete(route('workspaces.destroy', $workspace));
    $responseMember->assertStatus(403);

    // Stranger attempts delete
    $responseStranger = $this->actingAs($stranger)->delete(route('workspaces.destroy', $workspace));
    $responseStranger->assertStatus(403);

    // Ensure workspace still exists intact
    $this->assertDatabaseHas('workspaces', ['id' => $workspace->id]);
});

// FR-7: Validates workspace name: required, string, max 255 chars
test('validates workspace name input: required, string, max 255 chars (FR-7)', function () {
    $user = User::factory()->create(['status' => true]);

    // Empty name
    $responseEmpty = $this->actingAs($user)->post(route('workspaces.store'), [
        'name' => '',
    ]);
    $responseEmpty->assertSessionHasErrors('name');

    // Name exceeding 255 chars
    $responseLong = $this->actingAs($user)->post(route('workspaces.store'), [
        'name' => str_repeat('a', 256),
    ]);
    $responseLong->assertSessionHasErrors('name');

    // Valid 255 chars succeeds
    $responseValid = $this->actingAs($user)->post(route('workspaces.store'), [
        'name' => str_repeat('a', 255),
    ]);
    $responseValid->assertSessionHasNoErrors();
});

// FR-8: Displays clear error message if deletion fails
test('displays clear error message if workspace deletion fails (FR-8)', function () {
    $owner = User::factory()->create(['status' => true]);
    $workspace = Workspace::create(['name' => 'Failing Delete Workspace', 'owner_id' => $owner->id]);
    $workspace->members()->attach($owner->id, ['role' => WorkspaceRole::OWNER->value]);

    Workspace::deleting(function ($ws) {
        if ($ws->name === 'Failing Delete Workspace') {
            throw new Exception('Simulated delete error');
        }
    });

    $response = $this->actingAs($owner)->delete(route('workspaces.destroy', $workspace));

    $response->assertRedirect();
    $response->assertSessionHas('error');
    $this->assertDatabaseHas('workspaces', ['id' => $workspace->id]);
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

test('member cannot invite members from workspace', function () {
    $owner = User::factory()->create(['status' => true]);
    $member = User::factory()->create(['status' => true]);
    $targetUser = User::factory()->create(['status' => true]);

    $workspace = Workspace::create(['name' => 'Team Workspace 2', 'owner_id' => $owner->id]);
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
