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
        Schema::table('project_templates', function (Blueprint $table) {
            // Add company_id if it doesn't exist
            if (!Schema::hasColumn('project_templates', 'company_id')) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id');
            }
            
            // Add status if it doesn't exist
            if (!Schema::hasColumn('project_templates', 'status')) {
                $table->enum('status', ['active', 'inactive'])->default('active')->after('description');
            }
            
            // Add is_public if it doesn't exist
            if (!Schema::hasColumn('project_templates', 'is_public')) {
                $table->boolean('is_public')->default(false)->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_templates', function (Blueprint $table) {
            if (Schema::hasColumn('project_templates', 'company_id')) {
                $table->dropColumn('company_id');
            }
            if (Schema::hasColumn('project_templates', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('project_templates', 'is_public')) {
                $table->dropColumn('is_public');
            }
        });
    }
};