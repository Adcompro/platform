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
        // Drop project_ai_settings table if it exists
        Schema::dropIfExists('project_ai_settings');

        // Remove AI-related columns from time_entries table
        Schema::table('time_entries', function (Blueprint $table) {
            $table->dropColumn([
                'original_description',
                'ai_confidence'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate project_ai_settings table
        Schema::create('project_ai_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->boolean('use_global_settings')->default(true);
            $table->timestamps();
        });

        // Add AI-related columns back to time_entries table
        Schema::table('time_entries', function (Blueprint $table) {
            $table->text('original_description')->nullable()->after('description');
            $table->integer('ai_confidence')->nullable()->after('original_description');
        });
    }
};
