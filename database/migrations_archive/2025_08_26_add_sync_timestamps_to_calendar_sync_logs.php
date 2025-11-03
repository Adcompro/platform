<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('calendar_sync_logs', function (Blueprint $table) {
            $table->dateTime('sync_started_at')->nullable()->after('sync_to');
            $table->dateTime('sync_completed_at')->nullable()->after('sync_started_at');
            $table->integer('events_failed')->default(0)->after('events_deleted');
            
            // Add index for faster queries
            $table->index(['user_id', 'sync_completed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calendar_sync_logs', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'sync_completed_at']);
            $table->dropColumn(['sync_started_at', 'sync_completed_at', 'events_failed']);
        });
    }
};