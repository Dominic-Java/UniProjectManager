<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Security\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()?->isAdmin(), 403);

        return view('settings.index', [
            'title' => 'Setari',
            'users' => User::query()->orderByDesc('created_at')->limit(50)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
            'role' => ['required', 'in:profesor,student'],
        ]);

        $validated['email'] = strtolower($validated['email']);

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password_hash' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'member_code' => User::generateMemberCode($validated['role']),
            'is_active' => true,
        ]);

        AuditLogger::log('users.create', $request->user(), 'user', $user->id, [
            'role' => $user->role,
        ]);

        return back()->with('success', 'Contul a fost creat.');
    }

    public function updateUserRole(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $validated = $request->validate([
            'role' => ['required', 'in:profesor,student'],
        ]);

        if ($user->id === $request->user()->id && $validated['role'] !== 'profesor') {
            return back()->withErrors(['role' => 'Nu poti elimina rolul de profesor pentru contul tau.']);
        }

        $user->update(['role' => $validated['role']]);
        AuditLogger::log('users.role.update', $request->user(), 'user', $user->id, [
            'role' => $validated['role'],
        ]);

        return back()->with('success', 'Rolul a fost actualizat.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        if ($user->id === $request->user()->id) {
            return back()->withErrors(['user' => 'Nu poti sterge propriul cont.']);
        }

        $blocks = $this->getDeletionBlocks($user->id);
        if (!empty($blocks)) {
            return back()->withErrors([
                'user' => 'Utilizatorul nu poate fi sters deoarece are: ' . implode(', ', $blocks) . '.',
            ]);
        }

        $deletedId = $user->id;
        $deletedEmail = $user->email;
        $deletedRole = $user->role;

        $user->delete();

        AuditLogger::log('users.delete', $request->user(), 'user', $deletedId, [
            'email' => $deletedEmail,
            'role' => $deletedRole,
        ]);

        return back()->with('success', 'Utilizatorul a fost sters.');
    }

    private function getDeletionBlocks(int $userId): array
    {
        $blocks = [];

        if (DB::table('projects')->where('created_by', $userId)->exists()) {
            $blocks[] = 'proiecte create';
        }
        if (DB::table('teams')->where('created_by', $userId)->exists()) {
            $blocks[] = 'echipe create';
        }
        if (DB::table('milestones')->where('created_by', $userId)->exists()) {
            $blocks[] = 'milestone-uri create';
        }
        if (DB::table('deliverables')->where('created_by', $userId)->exists()) {
            $blocks[] = 'livrabile create';
        }
        if (DB::table('project_requirements')->where('created_by', $userId)->exists()) {
            $blocks[] = 'cerinte de proiect create';
        }
        if (DB::table('team_invitations')->where('invited_by', $userId)->exists()) {
            $blocks[] = 'invitatii trimise';
        }

        return $blocks;
    }
}
