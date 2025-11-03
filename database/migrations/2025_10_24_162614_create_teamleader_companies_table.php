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
        Schema::create('teamleader_companies', function (Blueprint $table) {
            $table->id();
            $table->string('teamleader_id')->unique()->index();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');

            // Company basic info
            $table->string('name');
            $table->string('vat_number')->nullable();
            $table->string('national_identification_number')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();

            // Address info
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();

            // Business info
            $table->string('business_type')->nullable();
            $table->string('status', 50)->index();
            $table->integer('employee_count')->nullable();

            // Raw data storage
            $table->json('raw_data')->nullable();

            // Import tracking
            $table->boolean('is_imported')->default(false)->index();
            $table->timestamp('synced_at')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();

            // Indexes voor performance
            $table->index(['is_imported', 'status']);
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teamleader_companies');
    }
};
