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
        Schema::create('project_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'on_hold'])
                  ->default('pending');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('sort_order')->default(0);
            $table->enum('fee_type', ['in_fee', 'extended'])->default('in_fee');
            $table->enum('pricing_type', ['fixed_price', 'hourly_rate'])->default('hourly_rate');
            $table->decimal('fixed_price', 10, 2)->nullable();
            $table->decimal('hourly_rate_override', 8, 2)->nullable();
            $table->decimal('estimated_hours', 8, 2)->nullable();
            $table->enum('invoicing_trigger', ['completion', 'approval', 'delivery'])
                  ->default('completion');
            $table->enum('source_type', ['manual', 'template', 'service'])->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->text('deliverables')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['project_id', 'sort_order']);
            $table->index(['project_id', 'status']);
            $table->index(['source_type', 'source_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_milestones');
    }
};