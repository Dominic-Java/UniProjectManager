<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('classrooms') || Schema::hasColumn('classrooms', 'study_year')) {
            return;
        }

        Schema::table('classrooms', function (Blueprint $table): void {
            $table->unsignedTinyInteger('study_year')->nullable()->after('subject');
            $table->index('study_year', 'idx_classrooms_study_year');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('classrooms') || !Schema::hasColumn('classrooms', 'study_year')) {
            return;
        }

        Schema::table('classrooms', function (Blueprint $table): void {
            try {
                $table->dropIndex('idx_classrooms_study_year');
            } catch (\Throwable $exception) {
                // Ignore when index was not created.
            }

            $table->dropColumn('study_year');
        });
    }
};

