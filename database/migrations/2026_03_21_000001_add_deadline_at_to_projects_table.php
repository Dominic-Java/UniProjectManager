<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('projects') || Schema::hasColumn('projects', 'deadline_at')) {
            return;
        }

        Schema::table('projects', function (Blueprint $table) {
            $table->dateTime('deadline_at')->nullable()->after('end_date');
            $table->index('deadline_at', 'idx_projects_deadline_at');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('projects') || !Schema::hasColumn('projects', 'deadline_at')) {
            return;
        }

        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex('idx_projects_deadline_at');
            $table->dropColumn('deadline_at');
        });
    }
};
