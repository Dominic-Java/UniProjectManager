<?php

use App\Models\Project;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('projects:close-expired {--dry-run : Afiseaza doar cate proiecte ar fi inchise}', function () {
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
        $this->info("Dry-run: {$count} proiect(e) ar fi inchise.");
        return self::SUCCESS;
    }

    $updated = $query->update([
        'status' => 'closed',
        'updated_at' => now(),
    ]);

    $this->info("Au fost inchise {$updated} proiect(e) expirate.");

    return self::SUCCESS;
})->purpose('Inchide proiectele care au depasit deadline-ul');

Schedule::command('projects:close-expired')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();
