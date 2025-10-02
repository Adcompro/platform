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
        // Add AI metadata to time_entries table
        Schema::table('time_entries', function (Blueprint $table) {
            $table->string('ai_suggested_subtask')->nullable()->after('description')->comment('AI-generated subtask name');
            $table->decimal('ai_confidence', 3, 2)->nullable()->after('ai_suggested_subtask')->comment('AI confidence score 0.00-1.00');
            $table->boolean('ai_suggestion_used')->default(false)->after('ai_confidence')->comment('Whether AI suggestion was used');
            $table->string('ai_feedback')->nullable()->after('ai_suggestion_used')->comment('User feedback: good, bad, adjusted');
            $table->string('user_adjusted_subtask')->nullable()->after('ai_feedback')->comment('User correction if AI was wrong');
            
            // Index for learning queries
            $table->index(['project_id', 'ai_feedback']);
            $table->index(['project_id', 'ai_suggestion_used']);
        });
        
        // Create learning feedback table for pattern recognition
        Schema::create('ai_learning_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('time_entry_id')->nullable()->constrained('time_entries')->onDelete('set null');
            $table->text('original_description')->comment('Original time entry description');
            $table->string('ai_suggestion')->comment('What AI suggested');
            $table->string('correct_subtask')->comment('The correct subtask name');
            $table->enum('feedback_type', ['positive', 'negative', 'correction'])->comment('Type of feedback');
            $table->text('learning_notes')->nullable()->comment('Notes for pattern learning');
            $table->decimal('confidence_before', 3, 2)->nullable()->comment('AI confidence before feedback');
            $table->decimal('confidence_after', 3, 2)->nullable()->comment('Adjusted confidence after feedback');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('applied_to_ai')->default(false)->comment('Whether this feedback was processed');
            $table->timestamps();
            
            // Indexes for efficient querying
            $table->index(['project_id', 'feedback_type']);
            $table->index(['project_id', 'applied_to_ai']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_learning_feedback');
        
        Schema::table('time_entries', function (Blueprint $table) {
            $table->dropColumn([
                'ai_suggested_subtask',
                'ai_confidence',
                'ai_suggestion_used',
                'ai_feedback',
                'user_adjusted_subtask'
            ]);
        });
    }
};