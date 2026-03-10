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
        ]);

        $remember = false;

        if (!Auth::attempt($credentials, $remember)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Credentiale invalide.']);
        }

        $request->session()->regenerate();
        $request->user()?->update(['last_login_at' => now()]);
        AuditLogger::log('auth.login', $request->user(), 'user', $request->user()?->id);

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
            'email' => ['required', 'email', 'max:255', $this->allowedEmailDomainRule()],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

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
            'role' => 'student',
        ]);
        Auth::login($user);
        $request->session()->regenerate();
        AuditLogger::log('auth.register', $user, 'user', $user->id);

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

    private function allowedEmailDomainRule(): \Closure
    {
        $allowed = [
            'gmail.com',
            'yahoo.com',
            'outlook.com',
            'gmx.com',
            'hotmail.com',
        ];

        return function (string $attribute, mixed $value, \Closure $fail) use ($allowed): void {
            $parts = explode('@', strtolower((string) $value));
            $domain = $parts[1] ?? '';
            if ($domain === '' || !in_array($domain, $allowed, true)) {
                $fail('Emailul trebuie sa fie dintr-un domeniu valid (gmail, yahoo, outlook, gmx, hotmail).');
            }
        };
    }
}
