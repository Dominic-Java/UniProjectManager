<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('deliverable_submissions')) {
            return;
        }

        Schema::table('deliverable_submissions', function (Blueprint $table) {
            if (!Schema::hasColumn('deliverable_submissions', 'grade_points')) {
                $table->decimal('grade_points', 8, 2)->nullable()->after('notes');
            }
            if (!Schema::hasColumn('deliverable_submissions', 'grade_feedback')) {
                $table->text('grade_feedback')->nullable()->after('grade_points');
            }
            if (!Schema::hasColumn('deliverable_submissions', 'graded_by_user_id')) {
                $table->unsignedBigInteger('graded_by_user_id')->nullable()->after('grade_feedback');
                $table->index('graded_by_user_id', 'idx_ds_graded_by');
            }
            if (!Schema::hasColumn('deliverable_submissions', 'graded_at')) {
                $table->dateTime('graded_at')->nullable()->after('graded_by_user_id');
            }
        });

        if (
            Schema::hasColumn('deliverable_submissions', 'graded_by_user_id')
            && Schema::hasTable('users')
        ) {
            try {
                Schema::table('deliverable_submissions', function (Blueprint $table) {
                    $table->foreign('graded_by_user_id', 'fk_ds_graded_by')
                        ->references('id')
                        ->on('users')
                        ->nullOnDelete();
                });
            } catch (\Throwable $exception) {
                // Keep migration idempotent when key/index already exists.
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('deliverable_submissions')) {
            return;
        }

        Schema::table('deliverable_submissions', function (Blueprint $table) {
            if (Schema::hasColumn('deliverable_submissions', 'graded_by_user_id')) {
                try {
                    $table->dropForeign('fk_ds_graded_by');
                } catch (\Throwable $exception) {
                    // Ignore when foreign key was not created.
                }

                try {
                    $table->dropIndex('idx_ds_graded_by');
                } catch (\Throwable $exception) {
                    // Ignore when index was not created.
                }

                $table->dropColumn('graded_by_user_id');
            }

            if (Schema::hasColumn('deliverable_submissions', 'graded_at')) {
                $table->dropColumn('graded_at');
            }
            if (Schema::hasColumn('deliverable_submissions', 'grade_feedback')) {
                $table->dropColumn('grade_feedback');
            }
            if (Schema::hasColumn('deliverable_submissions', 'grade_points')) {
                $table->dropColumn('grade_points');
            }
        });
    }
};

