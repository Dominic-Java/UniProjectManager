<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('classroom_grades')) {
            return;
        }

        Schema::create('classroom_grades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('classroom_id');
            $table->unsignedBigInteger('student_user_id');
            $table->unsignedBigInteger('graded_by_user_id')->nullable();
            $table->decimal('grade_value', 4, 2);
            $table->text('feedback')->nullable();
            $table->dateTime('last_warning_sent_at')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['classroom_id', 'student_user_id'], 'uq_classroom_grade_student');
            $table->index('grade_value', 'idx_classroom_grades_grade');
            $table->index('student_user_id', 'idx_classroom_grades_student');
            $table->index('graded_by_user_id', 'idx_classroom_grades_graded_by');

            $table->foreign('classroom_id', 'fk_classroom_grades_classroom')
                ->references('id')
                ->on('classrooms')
                ->onDelete('cascade');
            $table->foreign('student_user_id', 'fk_classroom_grades_student')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->foreign('graded_by_user_id', 'fk_classroom_grades_graded_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('classroom_grades')) {
            return;
        }

        Schema::drop('classroom_grades');
    }
};

