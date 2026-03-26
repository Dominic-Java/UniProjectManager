<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users') || Schema::hasColumn('users', 'locale_preference')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->string('locale_preference', 5)->default('ro')->after('theme_preference');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('users') || !Schema::hasColumn('users', 'locale_preference')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('locale_preference');
        });
    }
};
