<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Auth\PasswordResetService;
use App\Services\Security\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
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
        $hasThemePreferenceColumn = Schema::hasColumn('users', 'theme_preference');

        $validator = Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'birth_day' => ['nullable', 'integer', 'min:1', 'max:31'],
            'birth_month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'birth_year' => ['nullable', 'integer', 'min:1900', 'max:' . date('Y')],
            'gender' => ['nullable', 'in:male,female,other'],
            'city' => ['nullable', 'string', 'max:120'],
            'county' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
        ]);
        if ($hasThemePreferenceColumn) {
            $validator->addRules([
                'theme_preference' => ['nullable', 'in:light,dark'],
            ]);
        }

        $validator->after(function ($validator) use ($request) {
            $day = $request->input('birth_day');
            $month = $request->input('birth_month');
            $year = $request->input('birth_year');

            $anyProvided = $day !== null || $month !== null || $year !== null;
            if (!$anyProvided) {
                return;
            }

            if ($day === null || $month === null || $year === null) {
                $validator->errors()->add('birth_year', 'Te rugam sa completezi ziua, luna si anul nasterii.');
                return;
            }

            if (!checkdate((int) $month, (int) $day, (int) $year)) {
                $validator->errors()->add('birth_year', 'Data nasterii nu este valida. Verifica valorile introduse.');
            }
        });

        $validated = $validator->validate();

        $updates = $validated;
        unset($updates['avatar'], $updates['remove_avatar']);

        if ($request->boolean('remove_avatar')) {
            $this->deleteStoredAvatar($user->avatar_url);
            $updates['avatar_url'] = null;
        }

        if ($request->hasFile('avatar')) {
            $this->deleteStoredAvatar($user->avatar_url);

            $path = $request->file('avatar')->store('avatars/' . $user->id, 'public');
            $updates['avatar_url'] = Storage::url($path);
        }

        if (!$hasThemePreferenceColumn) {
            unset($updates['theme_preference']);
        }

        $user->update($updates);
        AuditLogger::log('profile.update', $user, 'user', $user->id);

        $theme = in_array(($validated['theme_preference'] ?? ''), ['light', 'dark'], true)
            ? $validated['theme_preference']
            : (
                $hasThemePreferenceColumn
                    ? ($user->theme_preference ?? 'light')
                    : (in_array((string) $request->cookie('upm_theme', 'light'), ['light', 'dark'], true) ? (string) $request->cookie('upm_theme', 'light') : 'light')
            );
        $secureCookie = $request->isSecure() || config('uniprojectmanager.force_https');

        return back()
            ->withCookie(cookie('upm_theme', $theme, 60 * 24 * 365, '/', null, $secureCookie, false, false, 'Lax'))
            ->with('success', 'Modificarile profilului au fost salvate.');
    }

    public function toggleTheme(Request $request): RedirectResponse
    {
        $user = $request->user();
        $hasThemePreferenceColumn = Schema::hasColumn('users', 'theme_preference');
        $currentTheme = $hasThemePreferenceColumn
            ? ($user->theme_preference ?? 'light')
            : (in_array((string) $request->cookie('upm_theme', 'light'), ['light', 'dark'], true) ? (string) $request->cookie('upm_theme', 'light') : 'light');
        $nextTheme = $currentTheme === 'dark' ? 'light' : 'dark';

        if ($hasThemePreferenceColumn) {
            $user->update([
                'theme_preference' => $nextTheme,
            ]);
        }

        AuditLogger::log('profile.theme.toggle', $user, 'user', $user->id, [
            'theme' => $nextTheme,
        ]);
        $secureCookie = $request->isSecure() || config('uniprojectmanager.force_https');

        return back()
            ->withCookie(cookie('upm_theme', $nextTheme, 60 * 24 * 365, '/', null, $secureCookie, false, false, 'Lax'))
            ->with('success', 'Tema interfetei a fost actualizata.');
    }

    public function sendPasswordResetLink(Request $request, PasswordResetService $passwordResetService): RedirectResponse
    {
        $user = $request->user();

        try {
            $passwordResetService->sendResetLink($user, $user);
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors(['profile' => 'Nu am putut trimite emailul de resetare. Verifica setarile SMTP si incearca din nou.']);
        }

        return back()->with('success', 'Ti-am trimis pe email linkul pentru resetarea parolei.');
    }

    private function deleteStoredAvatar(?string $avatarUrl): void
    {
        $path = $this->storagePathFromAvatarUrl($avatarUrl);
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function storagePathFromAvatarUrl(?string $avatarUrl): ?string
    {
        if (!$avatarUrl) {
            return null;
        }

        if (!str_starts_with($avatarUrl, '/storage/')) {
            return null;
        }

        $path = ltrim(substr($avatarUrl, strlen('/storage/')), '/');

        return $path !== '' ? $path : null;
    }
}
