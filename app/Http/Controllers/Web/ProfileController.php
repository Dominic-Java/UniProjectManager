<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Auth\PasswordResetService;
use App\Services\Security\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'title' => 'Profil',
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'theme_preference' => ['nullable', 'in:light,dark'],
            'birth_day' => ['nullable', 'integer', 'min:1', 'max:31'],
            'birth_month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'birth_year' => ['nullable', 'integer', 'min:1900', 'max:' . date('Y')],
            'gender' => ['nullable', 'in:male,female,other'],
            'city' => ['nullable', 'string', 'max:120'],
            'county' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $validator->after(function ($validator) use ($request) {
            $day = $request->input('birth_day');
            $month = $request->input('birth_month');
            $year = $request->input('birth_year');

            $anyProvided = $day !== null || $month !== null || $year !== null;
            if (!$anyProvided) {
                return;
            }

            if ($day === null || $month === null || $year === null) {
                $validator->errors()->add('birth_year', 'Completeaza ziua, luna si anul nasterii.');
                return;
            }

            if (!checkdate((int) $month, (int) $day, (int) $year)) {
                $validator->errors()->add('birth_year', 'Data nasterii nu este valida.');
            }
        });

        $validated = $validator->validate();

        $user->update($validated);
        AuditLogger::log('profile.update', $user, 'user', $user->id);

        $theme = in_array(($validated['theme_preference'] ?? ''), ['light', 'dark'], true)
            ? $validated['theme_preference']
            : ($user->theme_preference ?? 'light');

        return back()
            ->withCookie(cookie('upm_theme', $theme, 60 * 24 * 365, '/', null, false, false, false, 'Lax'))
            ->with('success', 'Profilul a fost actualizat.');
    }

    public function toggleTheme(Request $request): RedirectResponse
    {
        $user = $request->user();
        $nextTheme = ($user->theme_preference ?? 'light') === 'dark' ? 'light' : 'dark';

        $user->update([
            'theme_preference' => $nextTheme,
        ]);

        AuditLogger::log('profile.theme.toggle', $user, 'user', $user->id, [
            'theme' => $nextTheme,
        ]);

        return back()
            ->withCookie(cookie('upm_theme', $nextTheme, 60 * 24 * 365, '/', null, false, false, false, 'Lax'))
            ->with('success', 'Tema a fost actualizata.');
    }

    public function sendPasswordResetLink(Request $request, PasswordResetService $passwordResetService): RedirectResponse
    {
        $user = $request->user();

        try {
            $passwordResetService->sendResetLink($user, $user);
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors(['profile' => 'Nu am putut trimite emailul de resetare. Verifica setarile SMTP.']);
        }

        return back()->with('success', 'Ti-am trimis pe email linkul pentru resetarea parolei.');
    }
}
