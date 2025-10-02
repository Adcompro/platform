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
        // Voor MySQL moeten we de ENUM kolom opnieuw definiëren met de nieuwe waardes
        DB::statement("ALTER TABLE simplified_theme_settings MODIFY COLUMN font_size_base ENUM('10px', '11px', '12px', '13px', '14px', '15px', '16px') DEFAULT '12px'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Terugzetten naar originele waardes
        DB::statement("ALTER TABLE simplified_theme_settings MODIFY COLUMN font_size_base ENUM('12px', '13px', '14px', '15px', '16px') DEFAULT '14px'");
    }
};