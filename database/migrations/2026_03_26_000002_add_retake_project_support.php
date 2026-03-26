<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('projects') && !Schema::hasColumn('projects', 'is_retake_project')) {
            Schema::table('projects', function (Blueprint $table): void {
                $table->boolean('is_retake_project')->default(false)->after('visibility');
                $table->index('is_retake_project', 'idx_projects_is_retake');
            });
        }

        if (!Schema::hasTable('project_target_students')) {
            Schema::create('project_target_students', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('project_id');
                $table->unsignedBigInteger('student_user_id');
                $table->dateTime('created_at')->useCurrent();
                $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

                $table->unique(['project_id', 'student_user_id'], 'uq_project_target_student');
                $table->index('student_user_id', 'idx_project_target_student_user');

                $table->foreign('project_id', 'fk_project_target_project')
                    ->references('id')
                    ->on('projects')
                    ->onDelete('cascade');
                $table->foreign('student_user_id', 'fk_project_target_student')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('project_target_students')) {
            Schema::drop('project_target_students');
        }

        if (Schema::hasTable('projects') && Schema::hasColumn('projects', 'is_retake_project')) {
            Schema::table('projects', function (Blueprint $table): void {
                try {
                    $table->dropIndex('idx_projects_is_retake');
                } catch (\Throwable $exception) {
                    // Ignore when index was not created.
                }

                $table->dropColumn('is_retake_project');
            });
        }
    }
};

