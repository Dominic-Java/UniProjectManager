<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\HomeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Aici sunt definite rutele pentru interfața web (Blade views).
| Acestea sunt accesate prin browser.
|--------------------------------------------------------------------------
*/

// Pagina principală (home)
Route::get('/', [HomeController::class, 'index'])->name('home');

// Dashboard
Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

/*
|--------------------------------------------------------------------------
| Projects (temporar – până la implementarea controllerelor reale)
|--------------------------------------------------------------------------
*/

Route::get('/projects', function () {
    return 'TODO: Listă proiecte';
})->name('projects.index');

Route::get('/projects/create', function () {
    return 'TODO: Creează proiect';
})->name('projects.create');

/*
|--------------------------------------------------------------------------
| Teams
|--------------------------------------------------------------------------
*/

Route::get('/teams', function () {
    return 'TODO: Echipe';
})->name('teams.index');

/*
|--------------------------------------------------------------------------
| Deliverables
|--------------------------------------------------------------------------
*/

Route::get('/deliverables', function () {
    return 'TODO: Livrabile';
})->name('deliverables.index');

/*
|--------------------------------------------------------------------------
| Settings
|--------------------------------------------------------------------------
*/

Route::get('/settings', function () {
    return 'TODO: Setări';
})->name('settings');
