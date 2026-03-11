<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('last_login_at');
            }
            if (!Schema::hasColumn('users', 'email_verification_token')) {
                $table->string('email_verification_token', 64)->nullable()->after('email_verified_at');
            }
            if (!Schema::hasColumn('users', 'email_verification_expires_at')) {
                $table->timestamp('email_verification_expires_at')->nullable()->after('email_verification_token');
            }
        });

        DB::table('users')
            ->whereNull('email_verified_at')
            ->update([
                'email_verified_at' => now(),
                'email_verification_token' => null,
                'email_verification_expires_at' => null,
            ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'email_verification_expires_at')) {
                $table->dropColumn('email_verification_expires_at');
            }
            if (Schema::hasColumn('users', 'email_verification_token')) {
                $table->dropColumn('email_verification_token');
            }
            if (Schema::hasColumn('users', 'email_verified_at')) {
                $table->dropColumn('email_verified_at');
            }
        });
    }
};
