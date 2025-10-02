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
        Schema::create('simplified_theme_settings', function (Blueprint $table) {
            $table->id();
            
            // Optional company-specific themes
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade');
            
            // Colors (6 settings)
            $table->string('primary_color', 7)->default('#2563eb');
            $table->string('accent_color', 7)->default('#059669');
            $table->string('danger_color', 7)->default('#dc2626');
            $table->string('text_color', 7)->default('#1e293b');
            $table->string('muted_text_color', 7)->default('#64748b');
            $table->string('background_color', 7)->default('#f8fafc');
            
            // Typography (4 settings)
            $table->enum('font_family', ['system', 'inter', 'roboto', 'poppins', 'opensans'])->default('inter');
            $table->enum('font_size_base', ['10px', '11px', '12px', '13px', '14px', '15px', '16px'])->default('12px');
            $table->enum('header_font_size', ['small', 'normal', 'large'])->default('normal');
            $table->enum('line_height', ['compact', 'normal', 'relaxed'])->default('normal');
            
            // Buttons (4 settings)
            $table->enum('button_size', ['small', 'normal', 'large'])->default('normal');
            $table->enum('button_text_color', ['white', 'black', 'auto'])->default('white');
            $table->enum('button_radius', ['none', 'small', 'medium', 'large', 'full'])->default('medium');
            $table->enum('button_style', ['solid', 'outline', 'ghost'])->default('solid');
            
            // Tables (4 settings)
            $table->enum('table_row_padding', ['compact', 'normal', 'spacious'])->default('normal');
            $table->enum('table_header_style', ['light', 'dark', 'colored', 'bold'])->default('light');
            $table->boolean('table_striped')->default(false);
            $table->enum('table_hover_effect', ['none', 'light', 'dark', 'colored'])->default('light');
            
            // Layout (5 settings)
            $table->enum('header_height', ['compact', 'normal', 'tall'])->default('normal');
            $table->enum('sidebar_width', ['narrow', 'normal', 'wide'])->default('normal');
            $table->enum('card_padding', ['small', 'normal', 'large'])->default('normal');
            $table->enum('card_shadow', ['none', 'small', 'medium', 'large'])->default('small');
            $table->enum('border_radius', ['none', 'small', 'medium', 'large'])->default('medium');
            
            // Preset and meta
            $table->string('preset_name', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('name', 100)->default('Default Theme');
            
            $table->timestamps();
            
            // Indexes
            $table->index('company_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('simplified_theme_settings');
    }
};