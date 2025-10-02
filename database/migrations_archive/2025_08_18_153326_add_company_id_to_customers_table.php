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
        Schema::table('customers', function (Blueprint $table) {
            // Voeg company_id kolom toe
            $table->unsignedBigInteger('company_id')->nullable()->after('id');
            
            // Voeg foreign key constraint toe
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            
            // Voeg index toe voor performance
            $table->index('company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Verwijder foreign key constraint eerst
            $table->dropForeign(['company_id']);
            
            // Dan de kolom
            $table->dropColumn('company_id');
        });
    }
};