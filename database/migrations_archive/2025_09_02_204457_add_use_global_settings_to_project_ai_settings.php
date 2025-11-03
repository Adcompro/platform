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
        Schema::table('project_ai_settings', function (Blueprint $table) {
            $table->boolean('use_global_settings')->default(true)->after('project_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_ai_settings', function (Blueprint $table) {
            $table->dropColumn('use_global_settings');
        });
    }
};
