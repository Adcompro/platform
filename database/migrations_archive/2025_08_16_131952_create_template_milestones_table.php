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
        Schema::create('template_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_template_id')->constrained('project_templates')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->integer('days_from_start')->nullable();
            $table->integer('duration_days')->nullable();
            $table->enum('fee_type', ['in_fee', 'extended'])->default('in_fee');
            $table->enum('pricing_type', ['fixed_price', 'hourly_rate'])->default('hourly_rate');
            $table->decimal('default_fixed_price', 10, 2)->nullable();
            $table->decimal('default_hourly_rate', 8, 2)->nullable();
            $table->decimal('estimated_hours', 8, 2)->nullable();
            $table->text('deliverables')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['project_template_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_milestones');
    }
};