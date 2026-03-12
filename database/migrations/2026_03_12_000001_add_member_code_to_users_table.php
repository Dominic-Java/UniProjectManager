<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'member_code')) {
                $table->string('member_code', 30)->nullable()->unique();
            }
        });

        if (!Schema::hasColumn('users', 'member_code')) {
            return;
        }

        $users = DB::table('users')
            ->select('id', 'role', 'member_code')
            ->whereNull('member_code')
            ->get();

        foreach ($users as $user) {
            $role = $user->role === 'profesor' ? 'profesor' : 'student';
            $code = $this->generateUniqueMemberCode($role);

            DB::table('users')
                ->where('id', $user->id)
                ->update(['member_code' => $code]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'member_code')) {
                $table->dropUnique(['member_code']);
                $table->dropColumn('member_code');
            }
        });
    }

    private function generateUniqueMemberCode(string $role): string
    {
        $prefix = $role === 'profesor' ? 'PROF' : 'STU';

        do {
            $code = $prefix . '-' . Str::upper(Str::random(6));
        } while (DB::table('users')->where('member_code', $code)->exists());

        return $code;
    }
};
