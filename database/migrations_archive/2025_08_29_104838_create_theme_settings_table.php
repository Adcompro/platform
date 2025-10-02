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
        Schema::create('theme_settings', function (Blueprint $table) {
            $table->id();
            
            // Algemene branding
            $table->string('brand_name')->default('Progress Communications');
            $table->string('logo_path')->nullable();
            $table->string('favicon_path')->nullable();
            
            // Kleuren - Primary
            $table->string('primary_color')->default('#68757a');
            $table->string('primary_hover')->default('#576165');
            $table->string('primary_text')->default('#ffffff');
            
            // Kleuren - Secondary
            $table->string('secondary_color')->default('#ffffff');
            $table->string('secondary_hover')->default('#fff7ed'); // orange-50
            $table->string('secondary_border')->default('#d1d5db'); // gray-300
            
            // Kleuren - Accent (voor links en focus states)
            $table->string('accent_color')->default('#ea580c'); // orange-600
            $table->string('accent_hover')->default('#c2410c'); // orange-700
            
            // Text kleuren
            $table->string('text_primary')->default('#4b5563'); // gray-600
            $table->string('text_secondary')->default('#6b7280'); // gray-500
            $table->string('text_muted')->default('#9ca3af'); // gray-400
            
            // Typography
            $table->string('font_family')->default('Inter, system-ui, sans-serif');
            $table->string('font_size_base')->default('13px');
            $table->string('font_size_small')->default('11px');
            $table->string('font_size_large')->default('15px');
            $table->string('font_size_xl')->default('24px');
            
            // Spacing & Layout
            $table->string('header_padding')->default('py-4');
            $table->string('card_padding')->default('p-4');
            $table->string('button_padding')->default('px-4 py-2');
            $table->string('border_radius')->default('0.5rem'); // rounded-lg
            
            // Component styling
            $table->boolean('use_shadows')->default(true);
            $table->boolean('use_gradients')->default(false);
            $table->boolean('use_animations')->default(true);
            $table->string('transition_speed')->default('200ms');
            
            // Badge kleuren
            $table->string('badge_bg_color')->default('#68757a');
            $table->string('badge_text_color')->default('#ffffff');
            
            // Status kleuren (voor consistency)
            $table->string('success_color')->default('#10b981'); // green-500
            $table->string('warning_color')->default('#f59e0b'); // yellow-500
            $table->string('danger_color')->default('#ef4444'); // red-500
            $table->string('info_color')->default('#3b82f6'); // blue-500
            
            // Table styling
            $table->string('table_header_bg')->default('#f9fafb'); // gray-50
            $table->string('table_row_hover')->default('#f3f4f6'); // gray-100
            $table->string('table_border_color')->default('#e5e7eb'); // gray-200
            
            // Custom CSS (voor geavanceerde aanpassingen)
            $table->text('custom_css')->nullable();
            
            // Presets
            $table->string('theme_preset')->default('progress'); // progress, modern, classic, corporate, minimal
            
            // Active state
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
        });
        
        // Insert default theme
        DB::table('theme_settings')->insert([
            'brand_name' => 'Progress Communications',
            'theme_preset' => 'progress',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('theme_settings');
    }
};