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
        Schema::table('calendar_events', function (Blueprint $table) {
            // Make ms_event_id nullable for non-Microsoft providers
            $table->string('ms_event_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            // Revert back to NOT NULL (but this might fail if there are NULL values)
            $table->string('ms_event_id')->nullable(false)->change();
        });
    }
};
