<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
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

        return back()->with('success', 'Profilul a fost actualizat.');
    }
}
