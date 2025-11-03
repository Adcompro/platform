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
            // Add soft deletes column
            if (!Schema::hasColumn('services', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
            
            // Add missing columns we need for our Service model
            if (!Schema::hasColumn('services', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('is_public');
            }
            
            if (!Schema::hasColumn('services', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            }
        });

        // Add foreign key constraints if tables exist
        Schema::table('services', function (Blueprint $table) {
            if (Schema::hasTable('users')) {
                if (Schema::hasColumn('services', 'created_by')) {
                    try {
                        $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                    } catch (\Exception $e) {
                        // Foreign key exists already
                    }
                }
                
                if (Schema::hasColumn('services', 'updated_by')) {
                    try {
                        $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
                    } catch (\Exception $e) {
                        // Foreign key exists already
                    }
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            // Drop foreign keys first
            try {
                $table->dropForeign(['created_by']);
            } catch (\Exception $e) {
                // Foreign key doesn't exist
            }
            
            try {
                $table->dropForeign(['updated_by']);
            } catch (\Exception $e) {
                // Foreign key doesn't exist
            }

            // Drop columns
            $columnsToDrop = [];
            
            if (Schema::hasColumn('services', 'deleted_at')) {
                $columnsToDrop[] = 'deleted_at';
            }
            if (Schema::hasColumn('services', 'created_by')) {
                $columnsToDrop[] = 'created_by';
            }
            if (Schema::hasColumn('services', 'updated_by')) {
                $columnsToDrop[] = 'updated_by';
            }
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};