<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('audit_logs')) {
            return;
        }

        Schema::table('audit_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('audit_logs', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->index('user_id');
            }
            if (!Schema::hasColumn('audit_logs', 'action')) {
                $table->string('action', 80)->nullable()->after('user_id');
                $table->index('action');
            }
            if (!Schema::hasColumn('audit_logs', 'entity_type')) {
                $table->string('entity_type', 80)->nullable()->after('action');
                $table->index('entity_type');
            }
            if (!Schema::hasColumn('audit_logs', 'entity_id')) {
                $table->unsignedBigInteger('entity_id')->nullable()->after('entity_type');
                $table->index('entity_id');
            }
            if (!Schema::hasColumn('audit_logs', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('entity_id');
            }
            if (!Schema::hasColumn('audit_logs', 'user_agent')) {
                $table->string('user_agent', 255)->nullable()->after('ip_address');
            }
            if (!Schema::hasColumn('audit_logs', 'meta')) {
                $table->json('meta')->nullable()->after('user_agent');
            }
            if (!Schema::hasColumn('audit_logs', 'created_at')) {
                $table->dateTime('created_at')->useCurrent()->after('meta');
            }
        });
    }

    public function down(): void
    {
        // No-op: keep audit columns if added.
    }
};
