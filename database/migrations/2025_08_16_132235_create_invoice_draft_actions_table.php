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
        Schema::create('invoice_draft_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices');
            $table->foreignId('user_id')->constrained('users');
            $table->enum('action', [
                'created', 'line_added', 'line_removed', 'line_merged', 
                'description_changed', 'amount_adjusted', 'finalized'
            ]);
            $table->json('details')->nullable();
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['invoice_id', 'created_at']);
            $table->index(['user_id', 'action']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_draft_actions');
    }
};