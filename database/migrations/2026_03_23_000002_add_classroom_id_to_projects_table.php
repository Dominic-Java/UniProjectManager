<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('projects') || Schema::hasColumn('projects', 'classroom_id')) {
            return;
        }

        Schema::table('projects', function (Blueprint $table) {
            $table->unsignedBigInteger('classroom_id')->nullable()->after('domain');
            $table->index('classroom_id', 'idx_projects_classroom_id');
            $table->foreign('classroom_id', 'fk_projects_classroom_id')
                ->references('id')
                ->on('classrooms')
                ->nullOnDelete();
        });

        if (Schema::hasTable('classrooms')) {
            $classrooms = DB::table('classrooms')->get(['id', 'subject', 'created_by']);
            foreach ($classrooms as $classroom) {
                DB::table('projects')
                    ->whereNull('classroom_id')
                    ->where('created_by', $classroom->created_by)
                    ->where('domain', $classroom->subject)
                    ->update(['classroom_id' => $classroom->id]);
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('projects') || !Schema::hasColumn('projects', 'classroom_id')) {
            return;
        }

        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign('fk_projects_classroom_id');
            $table->dropIndex('idx_projects_classroom_id');
            $table->dropColumn('classroom_id');
        });
    }
};
