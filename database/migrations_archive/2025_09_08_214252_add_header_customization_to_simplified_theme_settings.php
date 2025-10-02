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
            // Check if columns exist before adding them
            if (!Schema::hasColumn('simplified_theme_settings', 'header_title_size')) {
                $table->enum('header_title_size', ['sm', 'base', 'lg', 'xl', '2xl', '3xl', '4xl'])->default('xl')->after('line_height');
            }
            if (!Schema::hasColumn('simplified_theme_settings', 'header_title_weight')) {
                $table->enum('header_title_weight', ['normal', 'medium', 'semibold', 'bold', 'extrabold'])->default('bold')->after('line_height');
            }
            if (!Schema::hasColumn('simplified_theme_settings', 'header_spacing')) {
                $table->enum('header_spacing', ['tight', 'normal', 'relaxed'])->default('normal')->after('line_height');
            }
            
            // Note: header_padding already exists in the table, so we skip it
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('simplified_theme_settings', function (Blueprint $table) {
            // Only drop columns that we added (not header_padding which already existed)
            $table->dropColumn([
                'header_title_size',
                'header_title_weight',
                'header_spacing'
            ]);
        });
    }
};
