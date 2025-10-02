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
            // Provider type voor multi-calendar support
            $table->enum('provider_type', ['microsoft', 'google', 'apple'])
                  ->default('microsoft')
                  ->after('user_id');

            // Index voor efficient querying
            $table->index(['user_id', 'provider_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calendar_sync_logs', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'provider_type']);
            $table->dropColumn('provider_type');
        });
    }
};
