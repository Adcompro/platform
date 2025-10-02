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
        Schema::table('time_entries', function (Blueprint $table) {
            // Rename ai_suggested_subtask to ai_improved_description
            $table->renameColumn('ai_suggested_subtask', 'ai_improved_description');
            
            // Add original description field
            $table->text('original_description')->nullable()->after('description')->comment('Original user input before AI improvement');
            
            // Remove subtask-related AI fields that are no longer needed
            $table->dropColumn('user_adjusted_subtask');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('time_entries', function (Blueprint $table) {
            $table->renameColumn('ai_improved_description', 'ai_suggested_subtask');
            $table->dropColumn('original_description');
            $table->string('user_adjusted_subtask')->nullable()->after('ai_feedback');
        });
    }
};