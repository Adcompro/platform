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
            // Voeg deleted_at kolom toe voor soft deletes
            if (!Schema::hasColumn('customers', 'deleted_at')) {
                $table->softDeletes();
            }
            
            // Voeg andere ontbrekende kolommen toe als ze nog niet bestaan
            if (!Schema::hasColumn('customers', 'status')) {
                $table->enum('status', ['active', 'inactive'])->default('active')->after('notes');
            }
            
            if (!Schema::hasColumn('customers', 'company_id')) {
                $table->unsignedBigInteger('company_id')->after('id')->nullable();
                $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
            
            if (Schema::hasColumn('customers', 'status')) {
                $table->dropColumn('status');
            }
            
            if (Schema::hasColumn('customers', 'company_id')) {
                $table->dropForeign(['company_id']);
                $table->dropColumn('company_id');
            }
        });
    }
};