<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('deliverables')) {
            return;
        }

        Schema::create('deliverables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('milestone_id')->nullable();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->dateTime('due_at')->nullable();
            $table->enum('submission_type', ['file', 'link', 'both'])->default('file');
            $table->decimal('max_points', 6, 2)->default(100.00);
            $table->unsignedBigInteger('created_by');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('project_id', 'fk_deliv_project');
            $table->index('milestone_id', 'fk_deliv_milestone');
            $table->index('created_by', 'fk_deliv_created_by');

            $table->foreign('project_id', 'fk_deliv_project')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('milestone_id', 'fk_deliv_milestone')->references('id')->on('milestones')->onDelete('set null');
            $table->foreign('created_by', 'fk_deliv_created_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliverables');
    }
};
