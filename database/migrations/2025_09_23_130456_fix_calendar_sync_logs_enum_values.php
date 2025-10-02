<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update sync_type ENUM to include 'full' value
        DB::statement("ALTER TABLE calendar_sync_logs MODIFY COLUMN sync_type ENUM('manual', 'automatic', 'webhook', 'full') NOT NULL");

        // Update status ENUM to include 'error' value
        DB::statement("ALTER TABLE calendar_sync_logs MODIFY COLUMN status ENUM('started', 'completed', 'failed', 'error', 'success') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original ENUM values
        DB::statement("ALTER TABLE calendar_sync_logs MODIFY COLUMN sync_type ENUM('manual', 'automatic', 'webhook') NOT NULL");
        DB::statement("ALTER TABLE calendar_sync_logs MODIFY COLUMN status ENUM('started', 'completed', 'failed') NOT NULL");
    }
};
