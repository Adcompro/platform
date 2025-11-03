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
        Schema::create('project_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('activity_type'); // created, updated, deleted, milestone_added, task_added, subtask_added, time_entry_added, status_changed, etc.
            $table->string('entity_type')->nullable(); // project, milestone, task, subtask, time_entry
            $table->unsignedBigInteger('entity_id')->nullable(); // ID van het betreffende entity
            $table->text('description');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            // Indexes voor snellere queries
            $table->index(['project_id', 'created_at']);
            $table->index('user_id');
            $table->index('activity_type');
            $table->index(['entity_type', 'entity_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_activities');
    }
};
