<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('projects')) {
            return;
        }

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->nullable()->unique();
            $table->string('title', 200);
            $table->text('description');
            $table->string('domain', 120)->nullable();
            $table->enum('status', ['draft', 'open', 'in_progress', 'closed', 'archived'])->default('draft');
            $table->enum('visibility', ['public', 'private'])->default('public');
            $table->unsignedTinyInteger('max_team_size')->default(4);
            $table->unsignedTinyInteger('min_team_size')->default(1);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('created_by', 'fk_project_created_by');
            $table->foreign('created_by', 'fk_project_created_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
