<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Performance optimalisatie indexen
     */
    public function up(): void
    {
        // Projects - voor sorting en filtering
        Schema::table('projects', function (Blueprint $table) {
            $table->index('created_at', 'projects_created_at_index');
            $table->index(['status', 'created_at'], 'projects_status_created_at_index');
        });

        // Time Entries - voor aggregatie queries
        Schema::table('time_entries', function (Blueprint $table) {
            $table->index(['project_id', 'status', 'entry_date'], 'time_entries_project_status_date_index');
        });

        // Project Monthly Fees - voor budget berekeningen
        Schema::table('project_monthly_fees', function (Blueprint $table) {
            $table->index(['project_id', 'status'], 'project_monthly_fees_project_status_index');
        });

        // Contacts - voor customer filtering
        if (Schema::hasTable('contacts')) {
            Schema::table('contacts', function (Blueprint $table) {
                $table->index('customer_id', 'contacts_customer_id_index');
                $table->index('is_active', 'contacts_is_active_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex('projects_created_at_index');
            $table->dropIndex('projects_status_created_at_index');
        });

        Schema::table('time_entries', function (Blueprint $table) {
            $table->dropIndex('time_entries_project_status_date_index');
        });

        Schema::table('project_monthly_fees', function (Blueprint $table) {
            $table->dropIndex('project_monthly_fees_project_status_index');
        });

        if (Schema::hasTable('contacts')) {
            Schema::table('contacts', function (Blueprint $table) {
                $table->dropIndex('contacts_customer_id_index');
                $table->dropIndex('contacts_is_active_index');
            });
        }
    }
};
