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
        Schema::create('teamleader_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('teamleader_id')->unique()->index();

            // Contact basic info
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('full_name')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('position')->nullable();
            $table->string('language', 10)->nullable();

            // Company relation (kan meerdere companies hebben)
            $table->json('companies')->nullable(); // Array van company IDs

            // Address info
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();

            // Social media
            $table->string('linkedin')->nullable();
            $table->string('twitter')->nullable();

            // Raw data storage
            $table->json('raw_data')->nullable();

            // Import tracking
            $table->boolean('is_imported')->default(false)->index();
            $table->timestamp('synced_at')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();

            // Indexes voor performance
            $table->index(['first_name', 'last_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teamleader_contacts');
    }
};
