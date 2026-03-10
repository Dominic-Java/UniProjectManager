<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('project_staff')) {
            return;
        }

        Schema::create('project_staff', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('professor_user_id');
            $table->enum('staff_role', ['coordinator', 'evaluator']);
            $table->dateTime('created_at')->useCurrent();

            $table->primary(['project_id', 'professor_user_id', 'staff_role']);
            $table->index('professor_user_id', 'fk_ps_prof');

            $table->foreign('project_id', 'fk_ps_project')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('professor_user_id', 'fk_ps_prof')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_staff');
    }
};
