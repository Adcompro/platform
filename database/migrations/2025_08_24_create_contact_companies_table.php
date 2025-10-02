<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contact_companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->boolean('is_primary')->default(false);
            $table->string('role')->nullable(); // Role at this company
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Unique constraint to prevent duplicate entries
            $table->unique(['contact_id', 'company_id']);
            
            // Index for performance
            $table->index(['contact_id', 'is_primary']);
        });

        // Migrate existing company_id to the new pivot table
        Schema::table('contacts', function (Blueprint $table) {
            // We'll keep company_id for backwards compatibility but mark it as deprecated
            // Data migration will be handled separately
        });

        // Migrate existing data
        DB::statement("
            INSERT INTO contact_companies (contact_id, company_id, is_primary, created_at, updated_at)
            SELECT id, company_id, true, NOW(), NOW()
            FROM contacts
            WHERE company_id IS NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_companies');
    }
};