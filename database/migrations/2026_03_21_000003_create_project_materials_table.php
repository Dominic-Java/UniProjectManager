<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('project_materials')) {
            return;
        }

        Schema::create('project_materials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('title', 200);
            $table->string('file_path', 500);
            $table->string('original_name', 255);
            $table->string('mime_type', 150)->nullable();
            $table->unsignedBigInteger('file_size_bytes')->default(0);
            $table->unsignedBigInteger('uploaded_by');
            $table->dateTime('uploaded_at')->useCurrent();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('project_id', 'idx_project_materials_project');
            $table->index('uploaded_by', 'idx_project_materials_uploaded_by');

            $table->foreign('project_id', 'fk_project_materials_project')
                ->references('id')
                ->on('projects')
                ->onDelete('cascade');
            $table->foreign('uploaded_by', 'fk_project_materials_uploaded_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_materials');
    }
};
