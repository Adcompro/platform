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
        Schema::table('social_media_mentions', function (Blueprint $table) {
            // Change engagement_rate from DECIMAL(5,2) to DECIMAL(10,2)
            // This allows values up to 99,999,999.99
            $table->decimal('engagement_rate', 10, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('social_media_mentions', function (Blueprint $table) {
            $table->decimal('engagement_rate', 5, 2)->nullable()->change();
        });
    }
};