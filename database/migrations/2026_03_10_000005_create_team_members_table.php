<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('team_members')) {
            return;
        }

        Schema::create('team_members', function (Blueprint $table) {
            $table->unsignedBigInteger('team_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('role', ['leader', 'member'])->default('member');
            $table->dateTime('joined_at')->useCurrent();
            $table->dateTime('left_at')->nullable();

            $table->primary(['team_id', 'user_id']);
            $table->index('user_id', 'fk_tm_user');

            $table->foreign('team_id', 'fk_tm_team')->references('id')->on('teams')->onDelete('cascade');
            $table->foreign('user_id', 'fk_tm_user')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_members');
    }
};
