<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'birth_year')) {
                $table->unsignedSmallInteger('birth_year')->nullable()->after('last_name');
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
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'birth_year')) {
                $table->dropColumn('birth_year');
            }
            if (Schema::hasColumn('users', 'gender')) {
                $table->dropColumn('gender');
            }
            if (Schema::hasColumn('users', 'city')) {
                $table->dropColumn('city');
            }
            if (Schema::hasColumn('users', 'county')) {
                $table->dropColumn('county');
            }
        });
    }
};
