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
            // Add only missing menu styling options
            if (!Schema::hasColumn('simplified_theme_settings', 'sidebar_background_color')) {
                $table->string('sidebar_background_color', 7)->default('#1e293b')->after('sidebar_style');
            }
            if (!Schema::hasColumn('simplified_theme_settings', 'sidebar_text_color')) {
                $table->string('sidebar_text_color', 7)->default('#94a3b8')->after('sidebar_background_color');
            }
            if (!Schema::hasColumn('simplified_theme_settings', 'sidebar_active_color')) {
                $table->string('sidebar_active_color', 7)->default('#14b8a6')->after('sidebar_text_color');
            }
            if (!Schema::hasColumn('simplified_theme_settings', 'top_nav_style')) {
                $table->enum('top_nav_style', ['tabs', 'pills', 'underline'])->default('tabs')->after('sidebar_active_color');
            }
            if (!Schema::hasColumn('simplified_theme_settings', 'top_nav_active_color')) {
                $table->string('top_nav_active_color', 7)->default('#14b8a6')->after('top_nav_style');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('simplified_theme_settings', function (Blueprint $table) {
            // Only drop columns that were added by this migration
            $columnsToCheck = [
                'sidebar_background_color',
                'sidebar_text_color',
                'sidebar_active_color',
                'top_nav_style',
                'top_nav_active_color'
            ];

            foreach ($columnsToCheck as $column) {
                if (Schema::hasColumn('simplified_theme_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
