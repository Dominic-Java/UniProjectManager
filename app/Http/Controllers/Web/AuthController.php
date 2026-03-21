<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\PasswordResetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use App\Services\Security\AuditLogger;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login', ['title' => 'Autentificare']);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'role' => ['required', 'in:student,profesor'],
        ]);

        $email = strtolower($credentials['email']);
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($credentials['password'], $user->password_hash)) {
            return back()
                ->withInput($request->only('email', 'role'))
                ->withErrors(['email' => 'Credentiale invalide.']);
        }

        if ($user->role !== $credentials['role']) {
            return back()
                ->withInput($request->only('email', 'role'))
                ->withErrors(['email' => 'Rolul selectat nu corespunde contului.']);
        }

        if (!$user->is_active) {
            return back()
                ->withInput($request->only('email', 'role'))
                ->withErrors(['email' => 'Contul este dezactivat.']);
        }

        Auth::login($user);
        $request->session()->regenerate();
        $user->update(['last_login_at' => now()]);
        AuditLogger::log('auth.login', $user, 'user', $user->id);

        return redirect()->intended(route('dashboard'));
    }

    public function showRegister(): View
    {
        return view('auth.register', ['title' => 'Inregistrare']);
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'confirmed', 'min:8'],
            'role' => ['required', 'in:student,profesor'],
        ]);

        $validated['email'] = strtolower($validated['email']);
        if (!$this->isEmailAllowedForRole($validated['email'], $validated['role'])) {
            return back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors(['email' => $this->roleEmailErrorMessage($validated['role'])]);
        }

        if (User::where('email', $validated['email'])->exists()) {
            return back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors(['email' => 'Nu s-a putut crea contul cu datele furnizate.']);
        }

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password_hash' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'member_code' => User::generateMemberCode($validated['role']),
        ]);
        Auth::login($user);
        $request->session()->regenerate();
        AuditLogger::log('auth.register', $user, 'user', $user->id, ['role' => $user->role]);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        AuditLogger::log('auth.logout', $request->user(), 'user', $request->user()?->id);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function showForgotPassword(): View
    {
        return view('auth.forgot', ['title' => 'Resetare parola']);
    }

    public function sendResetLink(Request $request, PasswordResetService $passwordResetService): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = strtolower($validated['email']);
        $user = User::where('email', $email)->first();

        if ($user) {
            try {
                $passwordResetService->sendResetLink($user);
                AuditLogger::log('auth.password_reset_request', $user, 'user', $user->id);
            } catch (\Throwable $exception) {
                report($exception);
                AuditLogger::log('auth.password_reset_request_failed', $user, 'user', $user->id);
            }
        }

        return back()->with('status', 'Daca emailul exista, vei primi un link de resetare.');
    }

    public function showResetPassword(Request $request): View
    {
        $token = trim((string) $request->query('token', ''));
        $email = strtolower(trim((string) $request->query('email', '')));

        if ($token === '' || $email === '') {
            abort(404);
        }

        return view('auth.reset', [
            'title' => 'Resetare parola',
            'token' => $token,
            'email' => $email,
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);
        $validated['email'] = strtolower($validated['email']);

        $record = DB::table('password_reset_tokens')
            ->where('email', $validated['email'])
            ->first();

        if (!$record) {
            return back()->withErrors(['email' => 'Token invalid sau expirat.']);
        }

        $tokenMatches = hash('sha256', $validated['token']) === $record->token;
        $tokenExpired = $record->created_at
            ? now()->diffInMinutes($record->created_at) > PasswordResetService::TOKEN_EXPIRATION_MINUTES
            : true;

        if (!$tokenMatches || $tokenExpired) {
            return back()->withErrors(['email' => 'Token invalid sau expirat.']);
        }

        $user = User::where('email', $validated['email'])->first();
        if (!$user) {
            return back()->withErrors(['email' => 'Contul nu a fost gasit.']);
        }

        $user->update([
            'password_hash' => Hash::make($validated['password']),
        ]);

        DB::table('password_reset_tokens')->where('email', $validated['email'])->delete();

        if (Schema::hasTable('sessions') && Schema::hasColumn('sessions', 'user_id')) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }

        if (Auth::check() && Auth::id() === $user->id) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        AuditLogger::log('auth.password_reset', $user, 'user', $user->id);

        return redirect()->route('login')->with('success', 'Parola a fost resetata. Sesiunea a fost inchisa, autentifica-te din nou.');
    }


    private function isEmailAllowedForRole(string $email, string $role): bool
    {
        $email = strtolower($email);
        $domain = explode('@', $email)[1] ?? '';

        if ($role === 'profesor') {
            $allowedEmails = array_map('strtolower', config('uniprojectmanager.professor_emails', []));
            if (in_array($email, $allowedEmails, true)) {
                return true;
            }

            $allowedDomains = array_merge(
                config('uniprojectmanager.student_domains', []),
                config('uniprojectmanager.institutional_domains', []),
                config('uniprojectmanager.professor_domains', [])
            );
            $allowedDomains = array_values(array_unique(array_map('strtolower', $allowedDomains)));

            if (empty($allowedDomains)) {
                return false;
            }

            return $domain !== '' && in_array($domain, $allowedDomains, true);
        }

        $studentDomains = array_merge(
            config('uniprojectmanager.student_domains', []),
            config('uniprojectmanager.institutional_domains', [])
        );
        $studentDomains = array_values(array_unique(array_map('strtolower', $studentDomains)));
        if (empty($studentDomains)) {
            return true;
        }

        return $domain !== '' && in_array($domain, $studentDomains, true);
    }

    private function roleEmailErrorMessage(string $role): string
    {
        if ($role === 'profesor') {
            return 'Emailul nu este autorizat pentru cont de profesor.';
        }

        return 'Emailul trebuie sa fie dintr-un domeniu valid pentru studenti.';
    }
}
