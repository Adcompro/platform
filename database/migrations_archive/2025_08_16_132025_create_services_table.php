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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_category_id')->constrained('service_categories');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('sku_code')->nullable();
            $table->decimal('total_price', 10, 2);
            $table->decimal('estimated_hours', 8, 2)->nullable();
            $table->boolean('is_package')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->index(['service_category_id', 'is_active']);
            $table->index('sku_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};