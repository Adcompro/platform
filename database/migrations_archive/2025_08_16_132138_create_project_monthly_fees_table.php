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
        Schema::create('project_monthly_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects');
            $table->integer('year');
            $table->integer('month');
            $table->decimal('base_monthly_fee', 12, 2);
            $table->decimal('rollover_from_previous', 12, 2)->default(0);
            $table->decimal('total_available_fee', 12, 2);
            $table->decimal('hours_worked', 8, 2)->default(0);
            $table->decimal('hours_value', 12, 2)->default(0);
            $table->decimal('amount_invoiced_from_fee', 12, 2)->default(0);
            $table->decimal('additional_costs_in_fee', 12, 2)->default(0);
            $table->decimal('additional_costs_outside_fee', 12, 2)->default(0);
            $table->decimal('total_invoiced', 12, 2)->default(0);
            $table->decimal('rollover_to_next', 12, 2)->default(0);
            $table->boolean('is_finalized')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['project_id', 'year', 'month']);
            
            // Indexes
            $table->index(['project_id', 'year', 'month']);
            $table->index(['year', 'month', 'is_finalized']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_monthly_fees');
    }
};