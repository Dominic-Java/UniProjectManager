<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            DB::table('users')->where('role', 'admin')->update(['role' => 'profesor']);
        }

        if (Schema::hasTable('roles')) {
            DB::table('roles')->where('name', 'admin')->delete();
        }
    }

    public function down(): void
    {
        // No-op: we intentionally do not restore admin roles.
    }
};
