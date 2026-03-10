<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\ProjectsController;
use App\Http\Controllers\Web\TeamsController;
use App\Http\Controllers\Web\DeliverablesController;
use App\Http\Controllers\Web\MilestonesController;
use App\Http\Controllers\Web\SettingsController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\ProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Rute pentru interfata web (Blade views)
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'landing'])->name('landing');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.submit');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('register.submit');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1')->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/projects', [ProjectsController::class, 'index'])->name('projects.index');
    Route::get('/projects/create', [ProjectsController::class, 'create'])
        ->middleware('role:admin,profesor')
        ->name('projects.create');
    Route::post('/projects', [ProjectsController::class, 'store'])
        ->middleware('role:admin,profesor')
        ->name('projects.store');
    Route::get('/projects/{project}', [ProjectsController::class, 'show'])->name('projects.show');
    Route::get('/projects/{project}/edit', [ProjectsController::class, 'edit'])
        ->middleware('role:admin,profesor')
        ->name('projects.edit');
    Route::put('/projects/{project}', [ProjectsController::class, 'update'])
        ->middleware('role:admin,profesor')
        ->name('projects.update');
    Route::delete('/projects/{project}', [ProjectsController::class, 'destroy'])
        ->middleware('role:admin,profesor')
        ->name('projects.destroy');

    Route::get('/teams', [TeamsController::class, 'index'])->name('teams.index');
    Route::get('/teams/create', [TeamsController::class, 'create'])->name('teams.create');
    Route::post('/teams', [TeamsController::class, 'store'])->name('teams.store');
    Route::get('/teams/{team}', [TeamsController::class, 'show'])->name('teams.show');
    Route::get('/teams/{team}/edit', [TeamsController::class, 'edit'])->name('teams.edit');
    Route::put('/teams/{team}', [TeamsController::class, 'update'])->name('teams.update');
    Route::delete('/teams/{team}', [TeamsController::class, 'destroy'])->name('teams.destroy');

    Route::post('/teams/{team}/invitations', [TeamsController::class, 'sendInvitation'])
        ->middleware('throttle:10,1')
        ->name('teams.invitations.send');
    Route::post('/team-invitations/{invitation}/respond', [TeamsController::class, 'respondInvitation'])
        ->middleware('throttle:10,1')
        ->name('teams.invitations.respond');
    Route::delete('/team-invitations/{invitation}', [TeamsController::class, 'cancelInvitation'])
        ->middleware('throttle:10,1')
        ->name('teams.invitations.cancel');
    Route::post('/teams/{team}/members', [TeamsController::class, 'addMember'])
        ->middleware('throttle:10,1')
        ->name('teams.members.add');
    Route::delete('/teams/{team}/members/{user}', [TeamsController::class, 'removeMember'])->name('teams.members.remove');
    Route::get('/deliverables', [DeliverablesController::class, 'index'])->name('deliverables.index');
    Route::get('/deliverables/create', [DeliverablesController::class, 'create'])->name('deliverables.create');
    Route::post('/deliverables', [DeliverablesController::class, 'store'])->name('deliverables.store');
    Route::get('/deliverables/{deliverable}', [DeliverablesController::class, 'show'])->name('deliverables.show');
    Route::get('/deliverables/{deliverable}/edit', [DeliverablesController::class, 'edit'])->name('deliverables.edit');
    Route::put('/deliverables/{deliverable}', [DeliverablesController::class, 'update'])->name('deliverables.update');
    Route::delete('/deliverables/{deliverable}', [DeliverablesController::class, 'destroy'])->name('deliverables.destroy');

    Route::get('/milestones', [MilestonesController::class, 'index'])->name('milestones.index');
    Route::get('/milestones/create', [MilestonesController::class, 'create'])->name('milestones.create');
    Route::post('/milestones', [MilestonesController::class, 'store'])->name('milestones.store');
    Route::get('/milestones/{milestone}', [MilestonesController::class, 'show'])->name('milestones.show');
    Route::get('/milestones/{milestone}/edit', [MilestonesController::class, 'edit'])->name('milestones.edit');
    Route::put('/milestones/{milestone}', [MilestonesController::class, 'update'])->name('milestones.update');
    Route::delete('/milestones/{milestone}', [MilestonesController::class, 'destroy'])->name('milestones.destroy');
    Route::get('/settings', [SettingsController::class, 'index'])
        ->middleware('role:admin')
        ->name('settings.index');
    Route::put('/settings/users/{user}', [SettingsController::class, 'updateUserRole'])
        ->middleware('role:admin')
        ->name('settings.users.update');
});
