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
            'can_create_team' => Project::query()->openForParticipation()->exists(),
        ]);
    }

    public function create(): View
    {
        $user = auth()->user();

        return view('teams.create', [
            'title' => 'Creeaza echipa',
            'projects' => Project::query()
                ->openForParticipation()
                ->orderByDesc('created_at')
                ->get(),
            'students' => $user?->hasRole('profesor')
                ? User::query()
                    ->where('role', 'student')
                    ->orderBy('first_name')
                    ->orderBy('last_name')
                    ->get(['id', 'first_name', 'last_name', 'email'])
                : collect(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'name' => ['required', 'string', 'max:150'],
            'status' => ['nullable', 'in:active,locked,archived'],
            'captain_user_id' => ['nullable', 'exists:users,id'],
        ]);

        $validated['name'] = trim($validated['name']);

        $request->validate([
            'name' => [
                Rule::unique('teams', 'name')->where(fn ($q) => $q->where('project_id', $validated['project_id'])),
            ],
        ]);

        $project = Project::findOrFail($validated['project_id']);
        if ($response = $this->blockIfProjectLocked($project)) {
            return $response;
        }

        $leaderId = $request->user()->id;

        if ($request->user()->hasRole('profesor')) {
            if (empty($validated['captain_user_id'])) {
                return back()
                    ->withInput()
                    ->withErrors(['captain_user_id' => 'Profesorul trebuie sa desemneze un capitan (student).']);
            }

            $captain = User::findOrFail((int) $validated['captain_user_id']);
            if (!$captain->hasRole('student')) {
                return back()
                    ->withInput()
                    ->withErrors(['captain_user_id' => 'Capitanul echipei trebuie sa aiba rolul student.']);
            }

            if ($this->isUserAlreadyInProjectTeam($project->id, $captain->id)) {
                return back()
                    ->withInput()
                    ->withErrors(['captain_user_id' => 'Studentul selectat este deja intr-o alta echipa pentru acest proiect.']);
            }

            $leaderId = $captain->id;
        } else {
            abort_unless($request->user()->hasRole('student'), 403);

            if ($this->isUserAlreadyInProjectTeam($project->id, $request->user()->id)) {
                return back()
                    ->withInput()
                    ->withErrors(['project_id' => 'Esti deja membru intr-o echipa pentru acest proiect.']);
            }
        }

        $status = $request->user()->hasRole('profesor')
            ? ($validated['status'] ?? 'active')
            : 'active';

        $team = Team::create([
            'project_id' => $validated['project_id'],
            'name' => $validated['name'],
            'status' => $status,
            'created_by' => $request->user()->id,
        ]);

        DB::table('team_members')->updateOrInsert(
            ['team_id' => $team->id, 'user_id' => $leaderId],
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
            'project_locked' => (bool) $team->project?->isLocked(),
        ]);
    }

    public function edit(Team $team): View|RedirectResponse
    {
        $this->abortIfCannotManage($team);
        if ($response = $this->blockIfTeamProjectLocked($team)) {
            return $response;
        }

        return view('teams.edit', [
            'title' => 'Editeaza echipa',
            'team' => $team,
        ]);
    }

    public function update(Request $request, Team $team): RedirectResponse
    {
        $this->abortIfCannotManage($team);
        if ($response = $this->blockIfTeamProjectLocked($team)) {
            return $response;
        }

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
        if ($response = $this->blockIfTeamProjectLocked($team)) {
            return $response;
        }

        $teamId = $team->id;
        $team->delete();
        AuditLogger::log('teams.delete', request()->user(), 'team', $teamId);

        return redirect()->route('teams.index')->with('success', 'Echipa a fost stearsa.');
    }

    public function sendInvitation(Request $request, Team $team): RedirectResponse
    {
        $this->abortIfCannotManage($team);
        if ($response = $this->blockIfTeamProjectLocked($team)) {
            return $response;
        }

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'message' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::where('email', $validated['email'])->first();
        if (!$user) {
            return back()->withErrors(['email' => 'Utilizatorul nu exista.']);
        }
        if (!$user->hasRole('student')) {
            return back()->withErrors(['email' => 'In echipe pot fi invitati doar studenti.']);
        }

        $team->loadMissing('project');

        if (!$team->project) {
            return back()->withErrors(['email' => 'Echipa nu are un proiect asociat valid.']);
        }

        $alreadyMember = DB::table('team_members')
            ->where('team_id', $team->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyMember) {
            return back()->withErrors(['email' => 'Utilizatorul este deja membru in echipa.']);
        }

        if ($this->isUserAlreadyInProjectTeam($team->project_id, $user->id, $team->id)) {
            return back()->withErrors(['email' => 'Studentul este deja intr-o alta echipa pe acest proiect.']);
        }

        if ($this->teamIsAtCapacity($team)) {
            return back()->withErrors(['email' => 'Echipa a atins numarul maxim de membri setat in proiect.']);
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

        $invitation->loadMissing('team.project');
        if (!$invitation->team?->project) {
            return back()->with('error', 'Invitatia nu are un proiect asociat valid.');
        }

        if ($response = $this->blockIfProjectLocked($invitation->team->project)) {
            return $response;
        }

        $validated = $request->validate([
            'action' => ['required', 'in:accept,reject'],
        ]);

        $status = $validated['action'] === 'accept' ? 'accepted' : 'rejected';

        if ($status === 'accepted') {
            if (!$request->user()->hasRole('student')) {
                return back()->with('error', 'Doar studentii pot accepta invitatii in echipa.');
            }

            if ($this->isUserAlreadyInProjectTeam($invitation->team->project_id, $request->user()->id, $invitation->team_id)) {
                return back()->with('error', 'Esti deja intr-o alta echipa pentru acest proiect.');
            }

            if ($this->teamIsAtCapacity($invitation->team)) {
                return back()->with('error', 'Echipa a atins numarul maxim de membri setat in proiect.');
            }

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
        if ($response = $this->blockIfTeamProjectLocked($team)) {
            return $response;
        }

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
        if ($response = $this->blockIfTeamProjectLocked($team)) {
            return $response;
        }

        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $validated['email'])->first();
        if (!$user) {
            return back()->withErrors(['email' => 'Utilizatorul nu exista.']);
        }
        if (!$user->hasRole('student')) {
            return back()->withErrors(['email' => 'In echipe pot fi adaugati doar studenti.']);
        }

        $alreadyMember = DB::table('team_members')
            ->where('team_id', $team->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyMember) {
            return back()->withErrors(['email' => 'Utilizatorul este deja membru.']);
        }

        if ($this->isUserAlreadyInProjectTeam($team->project_id, $user->id, $team->id)) {
            return back()->withErrors(['email' => 'Studentul este deja intr-o alta echipa pe acest proiect.']);
        }

        if ($this->teamIsAtCapacity($team)) {
            return back()->withErrors(['email' => 'Echipa a atins numarul maxim de membri setat in proiect.']);
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
        if ($response = $this->blockIfTeamProjectLocked($team)) {
            return $response;
        }

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

        if ($user->hasRole('profesor')) {
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

    private function blockIfTeamProjectLocked(Team $team): ?RedirectResponse
    {
        $team->loadMissing('project');

        if (!$team->project) {
            return back()->with('error', 'Echipa nu are un proiect asociat.');
        }

        return $this->blockIfProjectLocked($team->project);
    }

    private function blockIfProjectLocked(Project $project): ?RedirectResponse
    {
        if (!$project->isLocked()) {
            return null;
        }

        return back()->with('error', 'Proiectul este inchis dupa deadline. Nu mai pot fi facute modificari.');
    }

    private function teamIsAtCapacity(Team $team): bool
    {
        $team->loadMissing('project');

        if (!$team->project) {
            return true;
        }

        $membersCount = (int) DB::table('team_members')
            ->where('team_id', $team->id)
            ->count();

        return $membersCount >= (int) $team->project->max_team_size;
    }

    private function isUserAlreadyInProjectTeam(int $projectId, int $userId, ?int $excludeTeamId = null): bool
    {
        $query = DB::table('team_members')
            ->join('teams', 'teams.id', '=', 'team_members.team_id')
            ->where('teams.project_id', $projectId)
            ->where('team_members.user_id', $userId);

        if ($excludeTeamId) {
            $query->where('teams.id', '!=', $excludeTeamId);
        }

        return $query->exists();
    }
}
