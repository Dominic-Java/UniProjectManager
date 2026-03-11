@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Profil</div>
        <h1>Date personale</h1>
        <p>Actualizeaza informatiile de baza ale contului tau.</p>
    </section>

    <section class="grid">
        <div class="card span-8">
            @if (session('success'))
                <div class="notice success" style="margin-bottom:12px;">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="notice error" style="margin-bottom:12px;">
                    Verifica datele introduse si incearca din nou.
                </div>
            @endif

            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PUT')

                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:12px;">
                    <div>
                        <label class="label" for="first_name">Prenume</label>
                        <input class="input" id="first_name" type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" required>
                    </div>
                    <div>
                        <label class="label" for="last_name">Nume</label>
                        <input class="input" id="last_name" type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" required>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:12px;">
                    <div>
                        <label class="label" for="birth_day">Zi nastere</label>
                        <input class="input" id="birth_day" type="number" min="1" max="31" name="birth_day" value="{{ old('birth_day', $user->birth_day) }}">
                    </div>
                    <div>
                        <label class="label" for="birth_month">Luna nastere</label>
                        <input class="input" id="birth_month" type="number" min="1" max="12" name="birth_month" value="{{ old('birth_month', $user->birth_month) }}">
                    </div>
                    <div>
                        <label class="label" for="birth_year">An nastere</label>
                        <input class="input" id="birth_year" type="number" min="1900" max="{{ date('Y') }}" name="birth_year" value="{{ old('birth_year', $user->birth_year) }}">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:12px;">
                    <div>
                        <label class="label" for="gender">Sex</label>
                        <select class="input" id="gender" name="gender">
                            <option value="">-- selecteaza --</option>
                            <option value="male" @selected(old('gender', $user->gender) === 'male')>Masculin</option>
                            <option value="female" @selected(old('gender', $user->gender) === 'female')>Feminin</option>
                            <option value="other" @selected(old('gender', $user->gender) === 'other')>Altul</option>
                        </select>
                    </div>
                    <div>
                        <label class="label" for="phone">Telefon</label>
                        <input class="input" id="phone" type="text" name="phone" value="{{ old('phone', $user->phone) }}">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:12px;">
                    <div>
                        <label class="label" for="city">Localitate</label>
                        <input class="input" id="city" type="text" name="city" value="{{ old('city', $user->city) }}">
                    </div>
                    <div>
                        <label class="label" for="county">Judet</label>
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

                <button type="submit" class="btn btn-primary">Salveaza profil</button>
            </form>
        </div>

        <div class="card span-4">
            <h3>Cont</h3>
            <p class="muted">Emailul este legat de autentificare.</p>
            <div class="notice">{{ $user->email }}</div>
            <p class="muted" style="margin-top:10px;">Rol cont</p>
            <div class="pill" style="margin-top:6px;">{{ ucfirst($user->role) }}</div>
        </div>
    </section>
@endsection
