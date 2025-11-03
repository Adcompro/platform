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
        Schema::table('projects', function (Blueprint $table) {
            // Add recurring_series_id to group all projects in a recurring series
            $table->string('recurring_series_id', 50)->nullable()->after('parent_recurring_project_id');
            $table->index('recurring_series_id'); // Index for fast lookups
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['recurring_series_id']);
            $table->dropColumn('recurring_series_id');
        });
    }
};
