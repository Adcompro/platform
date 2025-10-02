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
        Schema::create('customer_companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->boolean('is_primary')->default(false)->comment('Is this the primary managing company');
            $table->string('role')->nullable()->comment('Role of company for this customer');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Ensure unique combination
            $table->unique(['customer_id', 'company_id']);
            
            // Index for performance
            $table->index(['customer_id', 'is_primary']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_companies');
    }
};