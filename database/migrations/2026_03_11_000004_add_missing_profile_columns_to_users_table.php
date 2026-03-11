<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'birth_day')) {
                $table->unsignedTinyInteger('birth_day')->nullable()->after('last_name');
            }
            if (!Schema::hasColumn('users', 'birth_month')) {
                $table->unsignedTinyInteger('birth_month')->nullable()->after('birth_day');
            }
            if (!Schema::hasColumn('users', 'birth_year')) {
                $table->unsignedSmallInteger('birth_year')->nullable()->after('birth_month');
            }
            if (!Schema::hasColumn('users', 'gender')) {
                $table->string('gender', 20)->nullable()->after('birth_year');
            }
            if (!Schema::hasColumn('users', 'city')) {
                $table->string('city', 120)->nullable()->after('gender');
            }
            if (!Schema::hasColumn('users', 'county')) {
                $table->string('county', 120)->nullable()->after('city');
            }
        });
    }

    public function down(): void
    {
        // No-op: keep profile columns if added.
    }
};
