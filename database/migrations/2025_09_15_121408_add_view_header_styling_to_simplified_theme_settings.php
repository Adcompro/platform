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
            // View header styling options
            $table->enum('view_header_title_size', ['small', 'medium', 'large'])->default('medium')->after('topbar_background_color');
            $table->enum('view_header_padding', ['compact', 'normal', 'spacious'])->default('normal')->after('view_header_title_size');
            $table->boolean('view_header_auto_scale')->default(false)->after('view_header_padding');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('simplified_theme_settings', function (Blueprint $table) {
            $table->dropColumn(['view_header_title_size', 'view_header_padding', 'view_header_auto_scale']);
        });
    }
};
