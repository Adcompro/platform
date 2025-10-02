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
        Schema::table('simplified_theme_settings', function (Blueprint $table) {
            // Topbar/Header background color (separate from main background)
            $table->string('topbar_background_color', 7)->nullable()->after('sidebar_text_size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('simplified_theme_settings', function (Blueprint $table) {
            $table->dropColumn('topbar_background_color');
        });
    }
};
