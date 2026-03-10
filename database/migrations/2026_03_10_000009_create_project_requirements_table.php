<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('project_requirements')) {
            return;
        }

        Schema::create('project_requirements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('title', 200);
            $table->text('description');
            $table->unsignedInteger('version')->default(1);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by');
            $table->dateTime('created_at')->useCurrent();

            $table->index('project_id', 'fk_pr_project');
            $table->index('created_by', 'fk_pr_created_by');

            $table->foreign('project_id', 'fk_pr_project')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('created_by', 'fk_pr_created_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_requirements');
    }
};
