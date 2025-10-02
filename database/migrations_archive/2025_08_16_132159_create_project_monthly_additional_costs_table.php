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
        Schema::create('project_monthly_additional_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects');
            $table->foreignId('additional_cost_id')->constrained('project_additional_costs');
            $table->integer('year');
            $table->integer('month');
            $table->decimal('amount', 10, 2);
            $table->enum('fee_type', ['in_fee', 'additional']);
            $table->decimal('prorated_amount', 10, 2);
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->unsignedBigInteger('invoice_line_id')->nullable();
            $table->boolean('is_invoiced')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Unique constraint met korte naam
            $table->unique(['additional_cost_id', 'year', 'month'], 'unique_monthly_cost');
            
            // Indexes
            $table->index(['project_id', 'year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_monthly_additional_costs');
    }
};