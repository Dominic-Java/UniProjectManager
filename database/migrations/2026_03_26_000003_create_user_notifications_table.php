<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_notifications')) {
            return;
        }

        Schema::create('user_notifications', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('type', 80)->nullable();
            $table->string('title', 180);
            $table->text('body')->nullable();
            $table->string('url', 500)->nullable();
            $table->dateTime('read_at')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index(['user_id', 'read_at'], 'idx_user_notifications_user_read');
            $table->foreign('user_id', 'fk_user_notifications_user')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('user_notifications')) {
            Schema::drop('user_notifications');
        }
    }
};

