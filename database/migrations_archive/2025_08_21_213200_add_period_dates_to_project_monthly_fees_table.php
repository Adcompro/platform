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
        // Check if the table exists and if period columns don't exist
        if (Schema::hasTable('project_monthly_fees')) {
            Schema::table('project_monthly_fees', function (Blueprint $table) {
                if (!Schema::hasColumn('project_monthly_fees', 'period_start')) {
                    $table->date('period_start')->nullable()->after('month');
                }
                if (!Schema::hasColumn('project_monthly_fees', 'period_end')) {
                    $table->date('period_end')->nullable()->after('period_start');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('project_monthly_fees')) {
            Schema::table('project_monthly_fees', function (Blueprint $table) {
                if (Schema::hasColumn('project_monthly_fees', 'period_start')) {
                    $table->dropColumn('period_start');
                }
                if (Schema::hasColumn('project_monthly_fees', 'period_end')) {
                    $table->dropColumn('period_end');
                }
            });
        }
    }
};