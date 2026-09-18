<?php

namespace App\Http\Controllers;

use App\Enums\WorkspaceRole;
use App\Http\Requests\Workspace\InviteMemberRequest;
use App\Http\Requests\Workspace\StoreWorkspaceRequest;
use App\Http\Requests\Workspace\UpdateWorkspaceRequest;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WorkspaceController extends Controller
{
    public function index(): View
    {
        $workspaces = auth()->user()->workspaces()->with(['projects.tasks', 'members'])->latest()->get();

        return view('workspaces.index', compact('workspaces'));
    }

    public function create(): View
    {
        return view('workspaces.create');
    }

    public function store(StoreWorkspaceRequest $request): RedirectResponse
    {
        $user = auth()->user();

        try {
            $workspace = DB::transaction(function () use ($request, $user) {
                $workspace = Workspace::create([
                    'name' => $request->validated('name'),
                    'owner_id' => $user->id,
                ]);

                // Auto insert owner to workspace_members (FR-2, FR-3)
                $workspace->members()->attach($user->id, [
                    'role' => WorkspaceRole::OWNER->value,
                ]);

                return $workspace;
            });

            return redirect()->route('workspaces.show', $workspace)
                ->with('success', 'Workspace berhasil dibuat.');
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal membuat workspace. Terjadi kesalahan pada sistem.');
        }
    }

    public function show(Workspace $workspace): View
    {
        $this->authorize('view', $workspace);

        $workspace->load(['projects.tasks', 'members']);

        // Check current user's role in this workspace
        $userMember = $workspace->members->where('id', auth()->id())->first();
        $userRole = $userMember ? $userMember->pivot->role : null;

        return view('workspaces.show', compact('workspace', 'userRole'));
    }

    public function edit(Workspace $workspace): View
    {
        $this->authorize('update', $workspace);

        return view('workspaces.edit', compact('workspace'));
    }

    public function update(UpdateWorkspaceRequest $request, Workspace $workspace): RedirectResponse
    {
        $this->authorize('update', $workspace);

        $workspace->update([
            'name' => $request->validated('name'),
        ]);

        return redirect()->route('workspaces.show', $workspace)
            ->with('success', 'Nama workspace berhasil diperbarui.');
    }

    public function destroy(Workspace $workspace): RedirectResponse
    {
        $this->authorize('delete', $workspace);

        try {
            DB::transaction(function () use ($workspace) {
                // Menghapus seluruh relasi keanggotaan dan workspace secara atomik (FR-5)
                $workspace->members()->detach();
                $workspace->delete();
            });

            return redirect()->route('workspaces.index')
                ->with('success', 'Workspace berhasil dihapus.');
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus workspace. Terjadi kesalahan pada sistem.');
        }
    }

    public function inviteMember(InviteMemberRequest $request, Workspace $workspace): RedirectResponse
    {
        $this->authorize('inviteMember', $workspace);

        $targetUser = User::where('email', $request->validated('email'))->firstOrFail();

        // Check if user is already a member
        $isMember = $workspace->members()->where('user_id', $targetUser->id)->exists();

        if ($isMember) {
            return redirect()->back()->with('error', "User {$targetUser->name} sudah menjadi anggota workspace ini.");
        }

        $workspace->members()->attach($targetUser->id, [
            'role' => WorkspaceRole::MEMBER->value,
        ]);

        return redirect()->back()
            ->with('success', "User {$targetUser->name} berhasil diundang sebagai Anggota.");
    }

    public function removeMember(Workspace $workspace, User $user): RedirectResponse
    {
        $this->authorize('removeMember', $workspace);

        // Check target user's role in workspace_members
        $memberPivot = $workspace->workspaceMembers()->where('user_id', $user->id)->first();

        if ($memberPivot && $memberPivot->role === WorkspaceRole::OWNER->value) {
            return redirect()->back()->with('error', 'Tidak dapat mengeluarkan Pemilik (Owner) workspace.');
        }

        $workspace->members()->detach($user->id);

        return redirect()->back()
            ->with('success', "User {$user->name} berhasil dikeluarkan dari workspace.");
    }
}
