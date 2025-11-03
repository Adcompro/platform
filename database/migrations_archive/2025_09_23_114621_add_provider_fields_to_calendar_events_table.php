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
            // Provider-agnostic fields voor multi-calendar support
            $table->enum('provider_type', ['microsoft', 'google', 'apple'])
                  ->default('microsoft')
                  ->after('user_id');

            // Generic external event ID (vervang ms_event_id geleidelijk)
            $table->string('external_event_id')
                  ->nullable()
                  ->after('provider_type');

            // Provider-specific raw data storage
            $table->json('provider_raw_data')
                  ->nullable()
                  ->after('ms_raw_data');

            // Index voor efficient querying
            $table->index(['provider_type', 'external_event_id']);
            $table->index(['user_id', 'provider_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->dropIndex(['provider_type', 'external_event_id']);
            $table->dropIndex(['user_id', 'provider_type']);
            $table->dropColumn(['provider_type', 'external_event_id', 'provider_raw_data']);
        });
    }
};
