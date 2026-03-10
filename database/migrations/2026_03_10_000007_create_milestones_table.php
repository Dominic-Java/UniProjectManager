<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('milestones')) {
            return;
        }

        Schema::create('milestones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->dateTime('due_at')->nullable();
            $table->decimal('weight', 5, 2)->default(0.00);
            $table->unsignedBigInteger('created_by');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('project_id', 'fk_ms_project');
            $table->index('created_by', 'fk_ms_created_by');

            $table->foreign('project_id', 'fk_ms_project')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('created_by', 'fk_ms_created_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('milestones');
    }
};
