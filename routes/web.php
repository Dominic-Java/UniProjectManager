<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\ProjectsController;
use App\Http\Controllers\Web\TeamsController;
use App\Http\Controllers\Web\DeliverablesController;
use App\Http\Controllers\Web\SettingsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Rute pentru interfata web (Blade views)
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');

Route::get('/projects', [ProjectsController::class, 'index'])->name('projects.index');
Route::get('/projects/create', [ProjectsController::class, 'create'])->name('projects.create');
Route::post('/projects', [ProjectsController::class, 'store'])->name('projects.store');

Route::get('/teams', [TeamsController::class, 'index'])->name('teams.index');
Route::get('/deliverables', [DeliverablesController::class, 'index'])->name('deliverables.index');
Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
