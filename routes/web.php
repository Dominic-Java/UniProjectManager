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
use App\Http\Controllers\Web\ProjectMaterialsController;
use App\Http\Controllers\Web\ClassroomsController;

/*
|--------------------------------------------------------------------------
| Web Routes (Blade views)
|--------------------------------------------------------------------------

*/

Route::get('/', [HomeController::class, 'landing'])->name('landing');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.submit');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->middleware('throttle:5,1')->name('password.email');
});

Route::get('/reset-password', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1')->name('password.update');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password-reset-link', [ProfileController::class, 'sendPasswordResetLink'])->name('profile.password-reset-link');
    Route::post('/profile/theme-toggle', [ProfileController::class, 'toggleTheme'])->name('profile.theme.toggle');

    Route::get('/classrooms', [ClassroomsController::class, 'index'])->name('classrooms.index');
    Route::get('/classrooms/create', [ClassroomsController::class, 'create'])
        ->middleware('role:profesor')
        ->name('classrooms.create');
    Route::post('/classrooms', [ClassroomsController::class, 'store'])
        ->middleware('role:profesor')
        ->name('classrooms.store');
    Route::post('/classrooms/join', [ClassroomsController::class, 'joinByCode'])
        ->middleware('role:student')
        ->name('classrooms.join');
    Route::get('/classrooms/{classroom}', [ClassroomsController::class, 'show'])->name('classrooms.show');
    Route::post('/classrooms/{classroom}/invitations', [ClassroomsController::class, 'sendInvitation'])
        ->middleware('role:profesor')
        ->name('classrooms.invitations.send');
    Route::post('/classroom-invitations/{invitation}/respond', [ClassroomsController::class, 'respondInvitation'])
        ->middleware('role:student')
        ->name('classrooms.invitations.respond');
    Route::delete('/classroom-invitations/{invitation}', [ClassroomsController::class, 'cancelInvitation'])
        ->middleware('role:profesor')
        ->name('classrooms.invitations.cancel');

    Route::get('/projects', [ProjectsController::class, 'index'])->name('projects.index');
    Route::get('/projects/create', [ProjectsController::class, 'create'])
        ->middleware('role:profesor')
        ->name('projects.create');
    Route::post('/projects', [ProjectsController::class, 'store'])
        ->middleware('role:profesor')
        ->name('projects.store');
    Route::get('/projects/{project}', [ProjectsController::class, 'show'])->name('projects.show');
    Route::post('/projects/{project}/materials', [ProjectMaterialsController::class, 'store'])
        ->middleware('role:profesor')
        ->name('projects.materials.store');
    Route::get('/project-materials/{material}/download', [ProjectMaterialsController::class, 'download'])
        ->name('projects.materials.download');
    Route::delete('/project-materials/{material}', [ProjectMaterialsController::class, 'destroy'])
        ->middleware('role:profesor')
        ->name('projects.materials.destroy');
    Route::get('/projects/{project}/edit', [ProjectsController::class, 'edit'])
        ->middleware('role:profesor')
        ->name('projects.edit');
    Route::put('/projects/{project}', [ProjectsController::class, 'update'])
        ->middleware('role:profesor')
        ->name('projects.update');
    Route::delete('/projects/{project}', [ProjectsController::class, 'destroy'])
        ->middleware('role:profesor')
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
    Route::get('/deliverables/create', [DeliverablesController::class, 'create'])
        ->middleware('role:profesor')
        ->name('deliverables.create');
    Route::post('/deliverables', [DeliverablesController::class, 'store'])
        ->middleware('role:profesor')
        ->name('deliverables.store');
    Route::get('/deliverables/{deliverable}', [DeliverablesController::class, 'show'])->name('deliverables.show');
    Route::post('/deliverables/{deliverable}/submit', [DeliverablesController::class, 'submit'])
        ->middleware('throttle:10,1')
        ->name('deliverables.submit');
    Route::get('/deliverable-submissions/{submission}/download', [DeliverablesController::class, 'downloadSubmission'])
        ->name('deliverables.submissions.download');
    Route::delete('/deliverable-submissions/{submission}', [DeliverablesController::class, 'cancelSubmission'])
        ->name('deliverables.submissions.cancel');
    Route::get('/deliverables/{deliverable}/edit', [DeliverablesController::class, 'edit'])
        ->middleware('role:profesor')
        ->name('deliverables.edit');
    Route::put('/deliverables/{deliverable}', [DeliverablesController::class, 'update'])
        ->middleware('role:profesor')
        ->name('deliverables.update');
    Route::delete('/deliverables/{deliverable}', [DeliverablesController::class, 'destroy'])
        ->middleware('role:profesor')
        ->name('deliverables.destroy');

    Route::get('/milestones', [MilestonesController::class, 'index'])->name('milestones.index');
    Route::get('/milestones/create', [MilestonesController::class, 'create'])
        ->middleware('role:profesor')
        ->name('milestones.create');
    Route::post('/milestones', [MilestonesController::class, 'store'])
        ->middleware('role:profesor')
        ->name('milestones.store');
    Route::get('/milestones/{milestone}', [MilestonesController::class, 'show'])->name('milestones.show');
    Route::get('/milestones/{milestone}/edit', [MilestonesController::class, 'edit'])
        ->middleware('role:profesor')
        ->name('milestones.edit');
    Route::put('/milestones/{milestone}', [MilestonesController::class, 'update'])
        ->middleware('role:profesor')
        ->name('milestones.update');
    Route::delete('/milestones/{milestone}', [MilestonesController::class, 'destroy'])
        ->middleware('role:profesor')
        ->name('milestones.destroy');
    Route::get('/settings', [SettingsController::class, 'index'])
        ->middleware('role:profesor')
        ->name('settings.index');
    Route::post('/settings/users', [SettingsController::class, 'store'])
        ->middleware('role:profesor')
        ->name('settings.users.store');
    Route::post('/settings/users/{user}/password-reset-link', [SettingsController::class, 'sendPasswordResetLink'])
        ->middleware('role:profesor')
        ->name('settings.users.password-reset-link');
    Route::put('/settings/users/{user}', [SettingsController::class, 'updateUserRole'])
        ->middleware('role:profesor')
        ->name('settings.users.update');
    Route::delete('/settings/users/{user}', [SettingsController::class, 'destroy'])
        ->middleware('role:profesor')
        ->name('settings.users.destroy');
});
