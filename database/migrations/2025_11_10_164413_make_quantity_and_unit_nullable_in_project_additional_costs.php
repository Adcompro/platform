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
        // Maak quantity en unit nullable (alleen nodig bij quantity_based calculation)
        DB::statement("ALTER TABLE project_additional_costs MODIFY quantity DECIMAL(10,2) NULL DEFAULT NULL");
        DB::statement("ALTER TABLE project_additional_costs MODIFY unit VARCHAR(50) NULL DEFAULT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert: maak quantity en unit weer NOT NULL met defaults
        DB::statement("ALTER TABLE project_additional_costs MODIFY quantity DECIMAL(10,2) NOT NULL DEFAULT 1.00");
        DB::statement("ALTER TABLE project_additional_costs MODIFY unit VARCHAR(50) NOT NULL DEFAULT 'piece'");
    }
};
