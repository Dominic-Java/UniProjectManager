<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_role')) {
            return;
        }

        Schema::create('user_role', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('role_id');

            $table->primary(['user_id', 'role_id']);
            $table->index('role_id', 'fk_ur_role');

            $table->foreign('user_id', 'fk_ur_user')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('role_id', 'fk_ur_role')->references('id')->on('roles')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_role');
    }
};
