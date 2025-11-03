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
        Schema::table('project_users', function (Blueprint $table) {
            // Check if columns don't exist before adding
            if (!Schema::hasColumn('project_users', 'can_log_time')) {
                $table->boolean('can_log_time')->default(true)->after('can_view_financials');
            }
            if (!Schema::hasColumn('project_users', 'can_approve_time')) {
                $table->boolean('can_approve_time')->default(false)->after('can_log_time');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_users', function (Blueprint $table) {
            $table->dropColumn(['can_log_time', 'can_approve_time']);
        });
    }
};
