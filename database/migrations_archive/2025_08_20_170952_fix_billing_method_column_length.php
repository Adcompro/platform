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
        // Fix billing_method kolom in project_companies tabel
        Schema::table('project_companies', function (Blueprint $table) {
            // Drop de huidige billing_method kolom en maak nieuwe met juiste lengte
            $table->dropColumn('billing_method');
        });
        
        Schema::table('project_companies', function (Blueprint $table) {
            // Voeg nieuwe billing_method kolom toe met juiste enum values
            $table->enum('billing_method', ['fixed_amount', 'actual_hours'])
                  ->default('actual_hours')
                  ->after('company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_companies', function (Blueprint $table) {
            $table->dropColumn('billing_method');
        });
        
        // Restore original (if needed)
        Schema::table('project_companies', function (Blueprint $table) {
            $table->string('billing_method')->default('actual_hours')->after('company_id');
        });
    }
};