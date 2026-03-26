@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">{{ __('ui.profile.page_title') }}</div>
        <h1>{{ __('ui.profile.page_heading') }}</h1>
        <p>{{ __('ui.profile.page_subtitle') }}</p>
    </section>

    <section class="grid">
        <div class="card span-8">
            @if (session('success'))
                <div class="notice success" style="margin-bottom:12px;">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="notice error" style="margin-bottom:12px;">
                    {{ __('ui.profile.validation_error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div style="margin-bottom:12px;">
                    <label class="label" for="avatar">{{ __('ui.profile.avatar') }}</label>
                    <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
                        @if(!empty($user->avatar_url))
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" style="width:64px;height:64px;border-radius:999px;object-fit:cover;border:1px solid var(--line);">
                        @else
                            <div style="width:64px;height:64px;border-radius:999px;border:1px solid var(--line);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:20px;">
                                {{ strtoupper(substr($user->first_name ?? $user->name, 0, 1)) }}
                            </div>
                        @endif
                        <div style="flex:1;min-width:240px;">
                            <input class="input" id="avatar" type="file" name="avatar" accept=".jpg,.jpeg,.png,.webp,.gif">
                            <label style="display:flex;gap:8px;align-items:center;margin-top:8px;font-size:13px;color:var(--muted);">
                                <input type="checkbox" name="remove_avatar" value="1">
                                {{ __('ui.profile.remove_avatar') }}
                            </label>
                        </div>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:12px;">
                    <div>
                        <label class="label" for="first_name">{{ __('ui.profile.first_name') }}</label>
                        <input class="input" id="first_name" type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" required>
                    </div>
                    <div>
                        <label class="label" for="last_name">{{ __('ui.profile.last_name') }}</label>
                        <input class="input" id="last_name" type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" required>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:12px;">
                    <div>
                        <label class="label" for="birth_day">{{ __('ui.profile.birth_day') }}</label>
                        <input class="input" id="birth_day" type="number" min="1" max="31" name="birth_day" value="{{ old('birth_day', $user->birth_day) }}">
                    </div>
                    <div>
                        <label class="label" for="birth_month">{{ __('ui.profile.birth_month') }}</label>
                        <input class="input" id="birth_month" type="number" min="1" max="12" name="birth_month" value="{{ old('birth_month', $user->birth_month) }}">
                    </div>
                    <div>
                        <label class="label" for="birth_year">{{ __('ui.profile.birth_year') }}</label>
                        <input class="input" id="birth_year" type="number" min="1900" max="{{ date('Y') }}" name="birth_year" value="{{ old('birth_year', $user->birth_year) }}">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:12px;">
                    <div>
                        <label class="label" for="gender">{{ __('ui.profile.gender') }}</label>
                        <select class="input" id="gender" name="gender">
                            <option value="">{{ __('ui.profile.select_option') }}</option>
                            <option value="male" @selected(old('gender', $user->gender) === 'male')>{{ __('ui.profile.gender_male') }}</option>
                            <option value="female" @selected(old('gender', $user->gender) === 'female')>{{ __('ui.profile.gender_female') }}</option>
                            <option value="other" @selected(old('gender', $user->gender) === 'other')>{{ __('ui.profile.gender_other') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="label" for="phone">{{ __('ui.profile.phone') }}</label>
                        <input class="input" id="phone" type="text" name="phone" value="{{ old('phone', $user->phone) }}">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:12px;">
                    <div>
                        <label class="label" for="theme_preference">{{ __('ui.profile.theme') }}</label>
                        <select class="input" id="theme_preference" name="theme_preference">
                            <option value="light" @selected(old('theme_preference', $user->theme_preference ?? 'light') === 'light')>{{ __('ui.layout.light_mode') }}</option>
                            <option value="dark" @selected(old('theme_preference', $user->theme_preference ?? 'light') === 'dark')>{{ __('ui.layout.dark_mode') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="label" for="locale_preference">{{ __('ui.profile.language') }}</label>
                        <select class="input" id="locale_preference" name="locale_preference">
                            <option value="ro" @selected(old('locale_preference', $user->locale_preference ?? app()->getLocale()) === 'ro')>{{ __('ui.profile.language_ro') }}</option>
                            <option value="en" @selected(old('locale_preference', $user->locale_preference ?? app()->getLocale()) === 'en')>{{ __('ui.profile.language_en') }}</option>
                        </select>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:12px;">
                    <div>
                        <label class="label" for="city">{{ __('ui.profile.city') }}</label>
                        <input class="input" id="city" type="text" name="city" value="{{ old('city', $user->city) }}">
                    </div>
                    <div>
                        <label class="label" for="county">{{ __('ui.profile.county') }}</label>
                        <input class="input" id="county" type="text" name="county" list="county-list" value="{{ old('county', $user->county) }}">
                    </div>
                </div>

                <datalist id="county-list">
                    <option value="Alba"></option>
                    <option value="Arad"></option>
                    <option value="Arges"></option>
                    <option value="Bacau"></option>
                    <option value="Bihor"></option>
                    <option value="Bistrita-Nasaud"></option>
                    <option value="Botosani"></option>
                    <option value="Brasov"></option>
                    <option value="Braila"></option>
                    <option value="Bucuresti"></option>
                    <option value="Buzau"></option>
                    <option value="Caras-Severin"></option>
                    <option value="Calarasi"></option>
                    <option value="Cluj"></option>
                    <option value="Constanta"></option>
                    <option value="Covasna"></option>
                    <option value="Dambovita"></option>
                    <option value="Dolj"></option>
                    <option value="Galati"></option>
                    <option value="Giurgiu"></option>
                    <option value="Gorj"></option>
                    <option value="Harghita"></option>
                    <option value="Hunedoara"></option>
                    <option value="Ialomita"></option>
                    <option value="Iasi"></option>
                    <option value="Ilfov"></option>
                    <option value="Maramures"></option>
                    <option value="Mehedinti"></option>
                    <option value="Mures"></option>
                    <option value="Neamt"></option>
                    <option value="Olt"></option>
                    <option value="Prahova"></option>
                    <option value="Salaj"></option>
                    <option value="Satu Mare"></option>
                    <option value="Sibiu"></option>
                    <option value="Suceava"></option>
                    <option value="Teleorman"></option>
                    <option value="Timis"></option>
                    <option value="Tulcea"></option>
                    <option value="Vaslui"></option>
                    <option value="Valcea"></option>
                    <option value="Vrancea"></option>
                </datalist>

                <button type="submit" class="btn btn-primary">{{ __('ui.profile.save_changes') }}</button>
            </form>
        </div>

        <div class="card span-4">
            <h3>{{ __('ui.profile.account') }}</h3>
            <p class="muted">{{ __('ui.profile.account_subtitle') }}</p>
            @if(!empty($user->avatar_url))
                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" style="width:74px;height:74px;border-radius:999px;object-fit:cover;border:1px solid var(--line);margin-bottom:10px;">
            @endif
            <div class="notice">{{ $user->email }}</div>
            <p class="muted" style="margin-top:10px;">{{ __('ui.profile.user_id') }}</p>
            <div class="notice">{{ $user->member_code ?? '-' }}</div>
            <p class="muted" style="margin-top:10px;">{{ __('ui.profile.role') }}</p>
            <div class="pill" style="margin-top:6px;">{{ ucfirst($user->role) }}</div>
            <form method="POST" action="{{ route('profile.password-reset-link') }}" style="margin-top:12px;">
                @csrf
                <button type="submit" class="btn btn-secondary">{{ __('ui.profile.send_password_reset') }}</button>
            </form>
        </div>
    </section>
@endsection
