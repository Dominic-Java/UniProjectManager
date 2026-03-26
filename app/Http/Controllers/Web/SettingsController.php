<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\PasswordResetService;
use App\Services\Auth\WelcomeMailService;
use App\Services\Security\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\Password;

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

    public function store(Request $request, WelcomeMailService $welcomeMailService): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(10)->letters()->mixedCase()->numbers()->symbols()],
            'role' => ['required', 'in:profesor,student'],
        ]);

        $validated['email'] = strtolower($validated['email']);

        $payload = [
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password_hash' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'member_code' => User::generateMemberCode($validated['role']),
            'is_active' => true,
        ];
        if (Schema::hasColumn('users', 'locale_preference')) {
            $payload['locale_preference'] = config('app.locale', 'ro');
        }

        $user = User::create($payload);

        $welcomeMailService->sendWelcomeMail($user, $request->user());

        AuditLogger::log('users.create', $request->user(), 'user', $user->id, [
            'role' => $user->role,
        ]);

        return back()->with('success', 'Contul a fost creat, iar linkul de setare a parolei a fost trimis pe email.');
    }

    public function updateUserRole(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $validated = $request->validate([
            'role' => ['required', 'in:profesor,student'],
        ]);

        if ($user->id === $request->user()->id && $validated['role'] !== 'profesor') {
            return back()->withErrors(['role' => 'Nu poti elimina rolul de profesor pentru propriul cont de administrator.']);
        }

        $oldRole = $user->role;
        $updates = ['role' => $validated['role']];
        $expectedPrefix = $validated['role'] === 'profesor' ? 'PROF-' : 'STU-';
        $hasMatchingPrefix = is_string($user->member_code) && str_starts_with($user->member_code, $expectedPrefix);

        if ($oldRole !== $validated['role'] || !$hasMatchingPrefix) {
            $updates['member_code'] = User::generateMemberCode($validated['role']);
        }

        $user->update($updates);
        AuditLogger::log('users.role.update', $request->user(), 'user', $user->id, [
            'old_role' => $oldRole,
            'role' => $validated['role'],
            'member_code' => $user->member_code,
        ]);

        return back()->with('success', 'Rolul a fost actualizat. Daca a fost necesar, ID-ul de utilizator a fost regenerat.');
    }

    public function sendPasswordResetLink(Request $request, User $user, PasswordResetService $passwordResetService): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        if (!$user->is_active) {
            return back()->withErrors(['user' => 'Contul este dezactivat. Reactiveaza contul inainte de a trimite resetarea parolei.']);
        }

        try {
            $passwordResetService->sendResetLink($user, $request->user());
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors(['user' => 'Nu am putut trimite emailul de resetare. Verifica setarile SMTP si incearca din nou.']);
        }

        return back()->with('success', 'Linkul de resetare a fost trimis catre ' . $user->email . '.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        if ($user->id === $request->user()->id) {
            return back()->withErrors(['user' => 'Nu iti poti sterge propriul cont de administrator.']);
        }
        if ($user->isAdmin()) {
            return back()->withErrors(['user' => 'Conturile de administrator nu pot fi sterse din aceasta sectiune.']);
        }

        $blocks = $this->getDeletionBlocks($user->id);
        if (!empty($blocks)) {
            return back()->withErrors([
                'user' => 'Utilizatorul nu poate fi eliminat deoarece are inregistrari active: ' . implode(', ', $blocks) . '.',
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

        return back()->with('success', 'Utilizatorul a fost eliminat.');
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
