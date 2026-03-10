<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use App\Services\Security\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TeamsController extends Controller
{
    public function index(Request $request): View
    {
        $teams = Team::with('project')
            ->withCount('members')
            ->orderByDesc('created_at')
            ->get();

        $invitations = TeamInvitation::with(['team.project', 'invitedBy'])
            ->where('invited_user_id', $request->user()->id)
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->get();

        return view('teams.index', [
            'title' => 'Echipe',
            'teams' => $teams,
            'invitations' => $invitations,
        ]);
    }

    public function create(): View
    {
        return view('teams.create', [
            'title' => 'Creeaza echipa',
            'projects' => Project::orderByDesc('created_at')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'name' => ['required', 'string', 'max:150'],
            'status' => ['nullable', 'in:active,locked,archived'],
        ]);

        $validated['name'] = trim($validated['name']);

        $request->validate([
            'name' => [
                Rule::unique('teams', 'name')->where(fn ($q) => $q->where('project_id', $validated['project_id'])),
            ],
        ]);

        $team = Team::create([
            'project_id' => $validated['project_id'],
            'name' => $validated['name'],
            'status' => $validated['status'] ?? 'active',
            'created_by' => $request->user()->id,
        ]);

        DB::table('team_members')->updateOrInsert(
            ['team_id' => $team->id, 'user_id' => $request->user()->id],
            ['role' => 'leader', 'joined_at' => now()]
        );

        AuditLogger::log('teams.create', $request->user(), 'team', $team->id);

        return redirect()->route('teams.show', $team)->with('success', 'Echipa a fost creata.');
    }

    public function show(Team $team): View
    {
        $team->load(['project', 'members', 'createdBy']);

        $invitations = TeamInvitation::with(['invitedUser', 'invitedBy'])
            ->where('team_id', $team->id)
            ->orderByDesc('created_at')
            ->get();

        return view('teams.show', [
            'title' => 'Detalii echipa',
            'team' => $team,
            'invitations' => $invitations,
            'can_manage' => $this->canManageTeam(request()->user(), $team),
        ]);
    }

    public function edit(Team $team): View
    {
        $this->abortIfCannotManage($team);

        return view('teams.edit', [
            'title' => 'Editeaza echipa',
            'team' => $team,
        ]);
    }

    public function update(Request $request, Team $team): RedirectResponse
    {
        $this->abortIfCannotManage($team);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'status' => ['required', 'in:active,locked,archived'],
        ]);

        $request->validate([
            'name' => [
                Rule::unique('teams', 'name')
                    ->where(fn ($q) => $q->where('project_id', $team->project_id))
                    ->ignore($team->id),
            ],
        ]);

        $team->update($validated);
        AuditLogger::log('teams.update', $request->user(), 'team', $team->id);

        return redirect()->route('teams.show', $team)->with('success', 'Echipa a fost actualizata.');
    }

    public function destroy(Team $team): RedirectResponse
    {
        $this->abortIfCannotManage($team);

        $teamId = $team->id;
        $team->delete();
        AuditLogger::log('teams.delete', request()->user(), 'team', $teamId);

        return redirect()->route('teams.index')->with('success', 'Echipa a fost stearsa.');
    }

    public function sendInvitation(Request $request, Team $team): RedirectResponse
    {
        $this->abortIfCannotManage($team);

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'message' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::where('email', $validated['email'])->first();
        if (!$user) {
            return back()->withErrors(['email' => 'Utilizatorul nu exista.']);
        }

        $alreadyMember = DB::table('team_members')
            ->where('team_id', $team->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyMember) {
            return back()->withErrors(['email' => 'Utilizatorul este deja membru in echipa.']);
        }

        $pendingInvite = TeamInvitation::where('team_id', $team->id)
            ->where('invited_user_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        if ($pendingInvite) {
            return back()->withErrors(['email' => 'Exista deja o invitatie activa pentru acest utilizator.']);
        }

        TeamInvitation::create([
            'team_id' => $team->id,
            'invited_user_id' => $user->id,
            'invited_by' => $request->user()->id,
            'status' => 'pending',
            'message' => $validated['message'] ?? null,
            'expires_at' => now()->addDays(7),
            'created_at' => now(),
        ]);

        AuditLogger::log('teams.invitation.send', $request->user(), 'team', $team->id, [
            'invited_user_id' => $user->id,
        ]);

        return back()->with('success', 'Invitatia a fost trimisa.');
    }

    public function respondInvitation(Request $request, TeamInvitation $invitation): RedirectResponse
    {
        if ($invitation->invited_user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'action' => ['required', 'in:accept,reject'],
        ]);

        $status = $validated['action'] === 'accept' ? 'accepted' : 'rejected';

        if ($status === 'accepted') {
            DB::table('team_members')->updateOrInsert(
                ['team_id' => $invitation->team_id, 'user_id' => $request->user()->id],
                ['role' => 'member', 'joined_at' => now()]
            );
        }

        $invitation->update([
            'status' => $status,
            'responded_at' => now(),
        ]);

        AuditLogger::log('teams.invitation.respond', $request->user(), 'team', $invitation->team_id, [
            'status' => $status,
        ]);

        return back()->with('success', 'Invitatia a fost ' . ($status === 'accepted' ? 'acceptata' : 'respinsa') . '.');
    }

    public function cancelInvitation(TeamInvitation $invitation): RedirectResponse
    {
        $team = Team::findOrFail($invitation->team_id);
        $this->abortIfCannotManage($team);

        $invitation->update([
            'status' => 'canceled',
            'responded_at' => now(),
        ]);

        AuditLogger::log('teams.invitation.cancel', request()->user(), 'team', $team->id, [
            'invited_user_id' => $invitation->invited_user_id,
        ]);

        return back()->with('success', 'Invitatia a fost anulata.');
    }

    public function addMember(Request $request, Team $team): RedirectResponse
    {
        $this->abortIfCannotManage($team);

        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $validated['email'])->first();
        if (!$user) {
            return back()->withErrors(['email' => 'Utilizatorul nu exista.']);
        }

        $alreadyMember = DB::table('team_members')
            ->where('team_id', $team->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyMember) {
            return back()->withErrors(['email' => 'Utilizatorul este deja membru.']);
        }

        DB::table('team_members')->insert([
            'team_id' => $team->id,
            'user_id' => $user->id,
            'role' => 'member',
            'joined_at' => now(),
        ]);

        AuditLogger::log('teams.member.add', $request->user(), 'team', $team->id, [
            'user_id' => $user->id,
        ]);

        return back()->with('success', 'Membrul a fost adaugat.');
    }

    public function removeMember(Team $team, User $user): RedirectResponse
    {
        $this->abortIfCannotManage($team);

        DB::table('team_members')
            ->where('team_id', $team->id)
            ->where('user_id', $user->id)
            ->delete();

        AuditLogger::log('teams.member.remove', request()->user(), 'team', $team->id, [
            'user_id' => $user->id,
        ]);

        return back()->with('success', 'Membrul a fost eliminat.');
    }

    private function abortIfCannotManage(Team $team): void
    {
        if (!$this->canManageTeam(request()->user(), $team)) {
            abort(403);
        }
    }

    private function canManageTeam(?User $user, Team $team): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->hasRole('admin', 'profesor')) {
            return true;
        }

        if ($team->created_by === $user->id) {
            return true;
        }

        return DB::table('team_members')
            ->where('team_id', $team->id)
            ->where('user_id', $user->id)
            ->where('role', 'leader')
            ->exists();
    }
}
