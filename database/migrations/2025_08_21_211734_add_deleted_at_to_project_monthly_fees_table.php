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
        // Check if the table exists and if deleted_at column doesn't exist
        if (Schema::hasTable('project_monthly_fees') && !Schema::hasColumn('project_monthly_fees', 'deleted_at')) {
            Schema::table('project_monthly_fees', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('project_monthly_fees') && Schema::hasColumn('project_monthly_fees', 'deleted_at')) {
            Schema::table('project_monthly_fees', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};