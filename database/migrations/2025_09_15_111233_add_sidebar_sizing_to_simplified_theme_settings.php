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
            // Sidebar sizing options
            $table->enum('sidebar_icon_size', ['small', 'medium', 'large'])->default('medium')->after('top_nav_active_color');
            $table->enum('sidebar_text_size', ['small', 'medium', 'large'])->default('small')->after('sidebar_icon_size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('simplified_theme_settings', function (Blueprint $table) {
            $table->dropColumn(['sidebar_icon_size', 'sidebar_text_size']);
        });
    }
};
