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
        Schema::table('social_media_sources', function (Blueprint $table) {
            $table->string('name')->after('platform')->nullable();
            $table->timestamp('last_collected_at')->nullable()->after('last_checked_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('social_media_sources', function (Blueprint $table) {
            $table->dropColumn(['name', 'last_collected_at']);
        });
    }
};