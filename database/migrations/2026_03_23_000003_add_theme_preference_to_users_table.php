<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'theme_preference')) {
                $table->string('theme_preference', 10)->default('light')->after('member_code');
            }
        });

        if (Schema::hasColumn('users', 'theme_preference')) {
            DB::table('users')
                ->whereNull('theme_preference')
                ->update(['theme_preference' => 'light']);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('users') || !Schema::hasColumn('users', 'theme_preference')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('theme_preference');
        });
    }
};
