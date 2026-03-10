<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('team_invitations')) {
            return;
        }

        Schema::create('team_invitations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id');
            $table->unsignedBigInteger('invited_user_id');
            $table->unsignedBigInteger('invited_by');
            $table->enum('status', ['pending', 'accepted', 'rejected', 'canceled', 'expired'])->default('pending');
            $table->string('message', 255)->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('responded_at')->nullable();

            $table->index('team_id', 'fk_ti_team');
            $table->index('invited_user_id', 'fk_ti_invited');
            $table->index('invited_by', 'fk_ti_invited_by');

            $table->foreign('team_id', 'fk_ti_team')->references('id')->on('teams')->onDelete('cascade');
            $table->foreign('invited_user_id', 'fk_ti_invited')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('invited_by', 'fk_ti_invited_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_invitations');
    }
};
