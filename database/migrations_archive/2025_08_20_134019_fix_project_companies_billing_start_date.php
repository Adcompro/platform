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
        // Fix project_companies table - make billing_start_date nullable
        Schema::table('project_companies', function (Blueprint $table) {
            // Als billing_start_date bestaat, maak het nullable
            if (Schema::hasColumn('project_companies', 'billing_start_date')) {
                $table->date('billing_start_date')->nullable()->change();
            } else {
                // Als het niet bestaat, voeg het toe als nullable
                $table->date('billing_start_date')->nullable()->after('billing_method');
            }
            
            // Voeg andere mogelijk ontbrekende velden toe
            if (!Schema::hasColumn('project_companies', 'role')) {
                $table->string('role')->default('subcontractor')->after('company_id');
            }
            if (!Schema::hasColumn('project_companies', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('notes');
            }
            if (!Schema::hasColumn('project_companies', 'hourly_rate_override')) {
                $table->decimal('hourly_rate_override', 8, 2)->nullable()->after('hourly_rate');
            }
            if (!Schema::hasColumn('project_companies', 'monthly_fixed_amount')) {
                $table->decimal('monthly_fixed_amount', 10, 2)->nullable()->after('hourly_rate_override');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_companies', function (Blueprint $table) {
            // Revert changes - maar pas op met data loss!
            $table->dropColumn([
                'billing_start_date',
                'role', 
                'is_active',
                'hourly_rate_override',
                'monthly_fixed_amount'
            ]);
        });
    }
};