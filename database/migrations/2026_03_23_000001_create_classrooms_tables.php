<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('classrooms')) {
            Schema::create('classrooms', function (Blueprint $table) {
                $table->id();
                $table->string('code', 24)->unique();
                $table->string('name', 200);
                $table->string('subject', 120);
                $table->text('description')->nullable();
                $table->unsignedBigInteger('created_by');
                $table->boolean('is_active')->default(true);
                $table->dateTime('created_at')->useCurrent();
                $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

                $table->index('created_by', 'idx_classrooms_created_by');
                $table->foreign('created_by', 'fk_classrooms_created_by')
                    ->references('id')
                    ->on('users');
            });
        }

        if (!Schema::hasTable('classroom_members')) {
            Schema::create('classroom_members', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('classroom_id');
                $table->unsignedBigInteger('user_id');
                $table->enum('role', ['teacher', 'student'])->default('student');
                $table->dateTime('joined_at')->useCurrent();
                $table->dateTime('created_at')->useCurrent();
                $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

                $table->unique(['classroom_id', 'user_id'], 'uq_classroom_user');
                $table->index('user_id', 'idx_classroom_members_user');

                $table->foreign('classroom_id', 'fk_classroom_members_classroom')
                    ->references('id')
                    ->on('classrooms')
                    ->onDelete('cascade');
                $table->foreign('user_id', 'fk_classroom_members_user')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('classroom_invitations')) {
            Schema::create('classroom_invitations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('classroom_id');
                $table->unsignedBigInteger('student_user_id');
                $table->unsignedBigInteger('invited_by');
                $table->enum('status', ['pending', 'accepted', 'rejected', 'canceled'])->default('pending');
                $table->string('message', 255)->nullable();
                $table->dateTime('expires_at')->nullable();
                $table->dateTime('responded_at')->nullable();
                $table->dateTime('created_at')->useCurrent();
                $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

                $table->index(['classroom_id', 'status'], 'idx_classroom_inv_status');
                $table->index('student_user_id', 'idx_classroom_inv_student');
                $table->index('invited_by', 'idx_classroom_inv_invited_by');

                $table->foreign('classroom_id', 'fk_classroom_inv_classroom')
                    ->references('id')
                    ->on('classrooms')
                    ->onDelete('cascade');
                $table->foreign('student_user_id', 'fk_classroom_inv_student')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
                $table->foreign('invited_by', 'fk_classroom_inv_invited_by')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('classroom_invitations')) {
            Schema::drop('classroom_invitations');
        }

        if (Schema::hasTable('classroom_members')) {
            Schema::drop('classroom_members');
        }

        if (Schema::hasTable('classrooms')) {
            Schema::drop('classrooms');
        }
    }
};
