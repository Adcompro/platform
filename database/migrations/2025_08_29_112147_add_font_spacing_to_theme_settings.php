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
        // Only add this migration AFTER the button/link colors migration is run
        if (!\Schema::hasColumn('theme_settings', 'link_visited')) {
            // Skip this migration if the previous one hasn't been run yet
            return;
        }
        
        Schema::table('theme_settings', function (Blueprint $table) {
            // Font weight settings (font_family already exists)
            if (!\Schema::hasColumn('theme_settings', 'font_weight_normal')) {
                $table->string('font_weight_normal')->default('400')->after('font_family');
            }
            if (!\Schema::hasColumn('theme_settings', 'font_weight_medium')) {
                $table->string('font_weight_medium')->default('500')->after('font_weight_normal');
            }
            if (!\Schema::hasColumn('theme_settings', 'font_weight_semibold')) {
                $table->string('font_weight_semibold')->default('600')->after('font_weight_medium');
            }
            if (!\Schema::hasColumn('theme_settings', 'font_weight_bold')) {
                $table->string('font_weight_bold')->default('700')->after('font_weight_semibold');
            }
            
            // Spacing settings
            if (!\Schema::hasColumn('theme_settings', 'letter_spacing_tight')) {
                $table->string('letter_spacing_tight')->default('-0.025em')->after('font_weight_bold');
            }
            if (!\Schema::hasColumn('theme_settings', 'letter_spacing_normal')) {
                $table->string('letter_spacing_normal')->default('0')->after('letter_spacing_tight');
            }
            if (!\Schema::hasColumn('theme_settings', 'letter_spacing_wide')) {
                $table->string('letter_spacing_wide')->default('0.025em')->after('letter_spacing_normal');
            }
            if (!\Schema::hasColumn('theme_settings', 'line_height_tight')) {
                $table->string('line_height_tight')->default('1.25')->after('letter_spacing_wide');
            }
            if (!\Schema::hasColumn('theme_settings', 'line_height_normal')) {
                $table->string('line_height_normal')->default('1.5')->after('line_height_tight');
            }
            if (!\Schema::hasColumn('theme_settings', 'line_height_loose')) {
                $table->string('line_height_loose')->default('1.75')->after('line_height_normal');
            }
            
            // Padding & Margin presets
            if (!\Schema::hasColumn('theme_settings', 'spacing_xs')) {
                $table->string('spacing_xs')->default('0.25rem')->after('line_height_loose');
            }
            if (!\Schema::hasColumn('theme_settings', 'spacing_sm')) {
                $table->string('spacing_sm')->default('0.5rem')->after('spacing_xs');
            }
            if (!\Schema::hasColumn('theme_settings', 'spacing_md')) {
                $table->string('spacing_md')->default('1rem')->after('spacing_sm');
            }
            if (!\Schema::hasColumn('theme_settings', 'spacing_lg')) {
                $table->string('spacing_lg')->default('1.5rem')->after('spacing_md');
            }
            if (!\Schema::hasColumn('theme_settings', 'spacing_xl')) {
                $table->string('spacing_xl')->default('2rem')->after('spacing_lg');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('theme_settings', function (Blueprint $table) {
            $table->dropColumn([
                // Skip font_family as it already existed
                'font_weight_normal',
                'font_weight_medium', 
                'font_weight_semibold',
                'font_weight_bold',
                'letter_spacing_tight',
                'letter_spacing_normal',
                'letter_spacing_wide',
                'line_height_tight',
                'line_height_normal', 
                'line_height_loose',
                'spacing_xs',
                'spacing_sm',
                'spacing_md',
                'spacing_lg',
                'spacing_xl'
            ]);
        });
    }
};
