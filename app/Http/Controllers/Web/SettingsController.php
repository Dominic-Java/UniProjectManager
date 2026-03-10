<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Security\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        return view('settings.index', [
            'title' => 'Setari',
            'users' => User::query()->orderByDesc('created_at')->limit(50)->get(),
        ]);
    }

    public function updateUserRole(Request $request, User $user): RedirectResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $validated = $request->validate([
            'role' => ['required', 'in:admin,profesor,student'],
        ]);

        if ($user->id === $request->user()->id && $validated['role'] !== 'admin') {
            return back()->withErrors(['role' => 'Nu poti elimina rolul de admin pentru contul tau.']);
        }

        $user->update(['role' => $validated['role']]);
        AuditLogger::log('users.role.update', $request->user(), 'user', $user->id, [
            'role' => $validated['role'],
        ]);

        return back()->with('success', 'Rolul a fost actualizat.');
    }
}
