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
        if (Schema::hasTable('project_ai_settings')) {
            Schema::table('project_ai_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('project_ai_settings', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('use_global_settings');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('project_ai_settings')) {
            Schema::table('project_ai_settings', function (Blueprint $table) {
                if (Schema::hasColumn('project_ai_settings', 'is_active')) {
                    $table->dropColumn('is_active');
                }
            });
        }
    }
};