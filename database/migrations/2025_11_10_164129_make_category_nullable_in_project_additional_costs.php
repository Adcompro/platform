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
        // Maak category nullable door kolom opnieuw te definiëren
        DB::statement("ALTER TABLE project_additional_costs MODIFY category ENUM('hosting', 'software', 'licenses', 'services', 'other') NULL DEFAULT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert: maak category weer NOT NULL met default 'other'
        DB::statement("ALTER TABLE project_additional_costs MODIFY category ENUM('hosting', 'software', 'licenses', 'services', 'other') NOT NULL DEFAULT 'other'");
    }
};
