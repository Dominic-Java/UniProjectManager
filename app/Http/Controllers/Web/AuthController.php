<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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

        $existing = User::where('email', $validated['email'])->first();
        if ($existing) {
            if (Hash::check($validated['password'], $existing->password_hash)) {
                return back()
                    ->withInput($request->except(['password', 'password_confirmation']))
                    ->withErrors(['email' => 'Acest cont deja exista.']);
            }

            return back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors(['email' => 'Exista deja un cont cu acest email.']);
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

    public function sendResetLink(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $validated['email'])->first();
        if ($user) {
            $token = Str::random(64);
            $hashed = hash('sha256', $token);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $validated['email']],
                ['token' => $hashed, 'created_at' => now()]
            );

            AuditLogger::log('auth.password_reset_request', $user, 'user', $user->id);

            return back()->with([
                'status' => 'Daca emailul exista, vei primi un link de resetare.',
                'reset_link' => route('password.reset', ['token' => $token, 'email' => $validated['email']]),
            ]);
        }

        return back()->with('status', 'Daca emailul exista, vei primi un link de resetare.');
    }

    public function showResetPassword(Request $request): View
    {
        return view('auth.reset', [
            'title' => 'Resetare parola',
            'token' => $request->query('token'),
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $validated['email'])
            ->first();

        if (!$record) {
            return back()->withErrors(['email' => 'Token invalid sau expirat.']);
        }

        $tokenMatches = hash('sha256', $validated['token']) === $record->token;
        $tokenExpired = $record->created_at
            ? now()->diffInMinutes($record->created_at) > 60
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
        AuditLogger::log('auth.password_reset', $user, 'user', $user->id);

        return redirect()->route('login')->with('success', 'Parola a fost resetata. Te poti autentifica.');
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
