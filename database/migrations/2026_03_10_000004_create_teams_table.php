<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('teams')) {
            return;
        }

        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('name', 150);
            $table->unsignedBigInteger('created_by');
            $table->enum('status', ['active', 'locked', 'archived'])->default('active');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['project_id', 'name'], 'uq_team_name_in_project');
            $table->index('created_by', 'fk_team_created_by');

            $table->foreign('created_by', 'fk_team_created_by')->references('id')->on('users');
            $table->foreign('project_id', 'fk_team_project')->references('id')->on('projects')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
