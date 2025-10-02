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
        Schema::table('theme_settings', function (Blueprint $table) {
            // Button colors
            $table->string('button_primary_bg')->default('#68757a')->after('accent_hover');
            $table->string('button_primary_hover')->default('#576165')->after('button_primary_bg');
            $table->string('button_primary_text')->default('#ffffff')->after('button_primary_hover');
            $table->string('button_secondary_bg')->default('#ffffff')->after('button_primary_text');
            $table->string('button_secondary_hover')->default('#f9fafb')->after('button_secondary_bg');
            $table->string('button_secondary_text')->default('#4b5563')->after('button_secondary_hover');
            $table->string('button_secondary_border')->default('#d1d5db')->after('button_secondary_text');
            
            // Link colors
            $table->string('link_color')->default('#ea580c')->after('button_secondary_border');
            $table->string('link_hover')->default('#c2410c')->after('link_color');
            $table->string('link_visited')->default('#dc2626')->after('link_hover');
            
            // Additional button states
            $table->string('button_danger_bg')->default('#ef4444')->after('link_visited');
            $table->string('button_danger_hover')->default('#dc2626')->after('button_danger_bg');
            $table->string('button_success_bg')->default('#10b981')->after('button_danger_hover');
            $table->string('button_success_hover')->default('#059669')->after('button_success_bg');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('theme_settings', function (Blueprint $table) {
            $table->dropColumn([
                'button_primary_bg',
                'button_primary_hover', 
                'button_primary_text',
                'button_secondary_bg',
                'button_secondary_hover',
                'button_secondary_text',
                'button_secondary_border',
                'link_color',
                'link_hover',
                'link_visited',
                'button_danger_bg',
                'button_danger_hover',
                'button_success_bg',
                'button_success_hover'
            ]);
        });
    }
};