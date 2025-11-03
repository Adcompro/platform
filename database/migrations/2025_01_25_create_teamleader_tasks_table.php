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
        Schema::create('teamleader_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('teamleader_id')->unique();
            $table->string('teamleader_project_id')->index();
            $table->string('teamleader_milestone_id')->nullable()->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('completed')->default(false);
            $table->date('due_on')->nullable();
            $table->integer('estimated_duration_minutes')->nullable(); // in minutes
            $table->boolean('is_imported')->default(false);
            $table->timestamp('imported_at')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->json('raw_data')->nullable(); // Complete API response
            $table->timestamps();

            $table->index('is_imported');
            $table->index(['teamleader_project_id', 'synced_at']);
            $table->index(['teamleader_milestone_id', 'synced_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teamleader_tasks');
    }
};
