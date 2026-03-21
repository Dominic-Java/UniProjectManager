<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('deliverable_submissions')) {
            return;
        }

        Schema::create('deliverable_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('deliverable_id');
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('student_user_id');
            $table->string('file_path', 500);
            $table->string('original_name', 255);
            $table->string('mime_type', 150)->nullable();
            $table->unsignedBigInteger('file_size_bytes')->default(0);
            $table->text('notes')->nullable();
            $table->dateTime('submitted_at')->useCurrent();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['deliverable_id', 'student_user_id'], 'uq_deliverable_student');
            $table->index('project_id', 'idx_deliverable_submission_project');
            $table->index('student_user_id', 'idx_deliverable_submission_student');

            $table->foreign('deliverable_id', 'fk_ds_deliverable')
                ->references('id')
                ->on('deliverables')
                ->onDelete('cascade');
            $table->foreign('project_id', 'fk_ds_project')
                ->references('id')
                ->on('projects')
                ->onDelete('cascade');
            $table->foreign('student_user_id', 'fk_ds_student')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliverable_submissions');
    }
};
