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
        Schema::create('project_ai_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->unique()->constrained('projects')->onDelete('cascade');
            $table->text('ai_naming_rules')->nullable()->comment('Project-specific naming conventions');
            $table->json('ai_task_categories')->nullable()->comment('Custom task categories for this project');
            $table->text('ai_prompt_template')->nullable()->comment('Custom AI prompt template');
            $table->json('ai_keywords')->nullable()->comment('Important project-specific terms');
            $table->json('ai_example_patterns')->nullable()->comment('Examples of good naming patterns');
            $table->boolean('use_global_settings')->default(true)->comment('Fall back to global settings if true');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Index for quick lookups
            $table->index(['project_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_ai_settings');
    }
};