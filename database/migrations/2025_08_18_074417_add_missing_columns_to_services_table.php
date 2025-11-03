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
        Schema::table('services', function (Blueprint $table) {
            // Add company_id if it doesn't exist (at the end since we don't know the exact structure)
            if (!Schema::hasColumn('services', 'company_id')) {
                $table->unsignedBigInteger('company_id')->nullable();
            }
            
            // Add status if it doesn't exist
            if (!Schema::hasColumn('services', 'status')) {
                $table->enum('status', ['active', 'inactive'])->default('active');
            }
            
            // Add is_public if it doesn't exist
            if (!Schema::hasColumn('services', 'is_public')) {
                $table->boolean('is_public')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if (Schema::hasColumn('services', 'company_id')) {
                $table->dropColumn('company_id');
            }
            if (Schema::hasColumn('services', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('services', 'is_public')) {
                $table->dropColumn('is_public');
            }
        });
    }
};