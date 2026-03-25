<?php

use App\Models\Project;
use App\Services\Catalog\ClassroomGradeNotificationService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('projects:close-expired {--dry-run : Afiseaza doar cate proiecte ar fi arhivate}', function () {
    if (!Schema::hasTable('projects') || !Schema::hasColumn('projects', 'deadline_at')) {
        $this->warn('Tabela projects sau coloana deadline_at nu exista.');
        return self::SUCCESS;
    }

    $query = Project::query()
        ->whereNotNull('deadline_at')
        ->where('deadline_at', '<=', now())
        ->whereIn('status', ['draft', 'open', 'in_progress']);

    $count = (clone $query)->count();

    if ($this->option('dry-run')) {
        $this->info("Dry-run: {$count} proiect(e) ar fi arhivate.");
        return self::SUCCESS;
    }

    $updated = $query->update([
        'status' => 'archived',
        'updated_at' => now(),
    ]);

    $this->info("Au fost arhivate {$updated} proiect(e) expirate.");

    return self::SUCCESS;
})->purpose('Arhiveaza proiectele care au depasit deadline-ul');

Artisan::command('projects:prune-expired {--dry-run : Afiseaza doar cate proiecte ar fi mutate in arhiva}', function () {
    if (!Schema::hasTable('projects') || !Schema::hasColumn('projects', 'deadline_at')) {
        $this->warn('Tabela projects sau coloana deadline_at nu exista.');
        return self::SUCCESS;
    }

    $retentionHours = max(0, (int) config('uniprojectmanager.expired_project_retention_hours', 24));
    $pruneBefore = $retentionHours > 0 ? now()->subHours($retentionHours) : now();

    $query = Project::query()
        ->whereNotNull('deadline_at')
        ->where('deadline_at', '<=', $pruneBefore);

    $query->whereIn('status', ['closed']);

    $count = (clone $query)->count();

    if ($this->option('dry-run')) {
        $this->info("Dry-run: {$count} proiect(e) ar fi mutate in arhiva.");
        return self::SUCCESS;
    }

    $archived = $query->update([
        'status' => 'archived',
        'updated_at' => now(),
    ]);

    $this->info("Au fost mutate in arhiva {$archived} proiect(e) expirate.");

    return self::SUCCESS;
})->purpose('Muta in arhiva proiectele inchise dupa perioada de retentie');

Artisan::command('catalog:send-failing-grade-reminders {--limit=200 : Numar maxim de notificari trimise intr-o rulare}', function () {
    if (!Schema::hasTable('classroom_grades')) {
        $this->warn('Tabela classroom_grades nu exista. Ruleaza migrarile.');
        return self::SUCCESS;
    }

    /** @var ClassroomGradeNotificationService $service */
    $service = app(ClassroomGradeNotificationService::class);
    $sent = $service->sendScheduledWarnings((int) $this->option('limit'));

    $this->info("Au fost trimise {$sent} remindere pentru studenti restanti.");

    return self::SUCCESS;
})->purpose('Trimite remindere email catre studentii cu note sub 5');

Schedule::command('projects:close-expired')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('projects:prune-expired')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('catalog:send-failing-grade-reminders')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->runInBackground();
