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
        // Add missing deleted_at column to project_additional_costs if table exists
        if (Schema::hasTable('project_additional_costs') && !Schema::hasColumn('project_additional_costs', 'deleted_at')) {
            Schema::table('project_additional_costs', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add missing deleted_at column to project_monthly_additional_costs if table exists
        if (Schema::hasTable('project_monthly_additional_costs') && !Schema::hasColumn('project_monthly_additional_costs', 'deleted_at')) {
            Schema::table('project_monthly_additional_costs', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Check if invoice_id exists and if not, make it nullable since invoices table might not exist yet
        if (Schema::hasColumn('project_additional_costs', 'invoice_id')) {
            Schema::table('project_additional_costs', function (Blueprint $table) {
                // Temporarily drop foreign key if it exists
                try {
                    $table->dropForeign(['invoice_id']);
                } catch (\Exception $e) {
                    // Foreign key might not exist
                }
                
                // Make invoice_id nullable
                $table->bigInteger('invoice_id')->unsigned()->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_additional_costs', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('project_monthly_additional_costs', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};