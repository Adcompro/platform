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
        Schema::create('service_subtasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_task_id')->constrained('service_tasks')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->decimal('estimated_hours', 8, 2)->nullable();
            $table->boolean('included_in_price')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->index(['service_task_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_subtasks');
    }
};