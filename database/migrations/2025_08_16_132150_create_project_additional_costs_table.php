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
        Schema::create('project_additional_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects');
            $table->foreignId('created_by')->constrained('users');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('cost_type', ['one_time', 'monthly_recurring']);
            $table->enum('fee_type', ['in_fee', 'additional']);
            $table->decimal('amount', 10, 2);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->enum('category', ['hosting', 'software', 'licenses', 'services', 'other'])->default('other');
            $table->string('vendor')->nullable();
            $table->string('reference')->nullable();
            $table->boolean('auto_invoice')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['project_id', 'is_active']);
            $table->index(['project_id', 'cost_type', 'start_date']);
            $table->index(['category', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_additional_costs');
    }
};