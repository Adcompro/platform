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
        // Eerst checken welke kolommen al bestaan door de database structuur te bekijken
        $existingColumns = $this->getExistingColumns();
        
        Schema::table('service_categories', function (Blueprint $table) use ($existingColumns) {
            // Voeg alleen kolommen toe die nog niet bestaan
            if (!in_array('company_id', $existingColumns)) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id');
            }
            
            if (!in_array('status', $existingColumns)) {
                $table->enum('status', ['active', 'inactive'])->default('active')->after('description');
            }
            
            if (!in_array('icon', $existingColumns)) {
                $table->string('icon')->nullable()->after('sort_order');
            }
            
            if (!in_array('color', $existingColumns)) {
                $table->string('color')->nullable()->after('icon');
            }
            
            if (!in_array('settings', $existingColumns)) {
                $table->json('settings')->nullable()->after('color');
            }
            
            if (!in_array('created_by', $existingColumns)) {
                $table->unsignedBigInteger('created_by')->nullable()->after('settings');
            }
            
            if (!in_array('updated_by', $existingColumns)) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            }
            
            if (!in_array('deleted_at', $existingColumns)) {
                $table->softDeletes()->after('updated_at');
            }
        });

        // Voeg foreign key constraints toe alleen als de benodigde tabellen bestaan
        if (Schema::hasTable('companies') && in_array('company_id', $this->getExistingColumns())) {
            try {
                Schema::table('service_categories', function (Blueprint $table) {
                    $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
                });
            } catch (\Exception $e) {
                // Foreign key bestaat mogelijk al of companies tabel bestaat niet
            }
        }
        
        if (Schema::hasTable('users')) {
            $currentColumns = $this->getExistingColumns();
            
            if (in_array('created_by', $currentColumns)) {
                try {
                    Schema::table('service_categories', function (Blueprint $table) {
                        $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                    });
                } catch (\Exception $e) {
                    // Foreign key bestaat mogelijk al
                }
            }
            
            if (in_array('updated_by', $currentColumns)) {
                try {
                    Schema::table('service_categories', function (Blueprint $table) {
                        $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
                    });
                } catch (\Exception $e) {
                    // Foreign key bestaat mogelijk al
                }
            }
        }

        // Voeg indexes toe voor betere performance
        $finalColumns = $this->getExistingColumns();
        
        if (in_array('company_id', $finalColumns) && in_array('status', $finalColumns)) {
            try {
                Schema::table('service_categories', function (Blueprint $table) {
                    $table->index(['company_id', 'status'], 'idx_service_categories_company_status');
                });
            } catch (\Exception $e) {
                // Index bestaat mogelijk al
            }
        }
        
        if (in_array('company_id', $finalColumns) && in_array('is_active', $finalColumns)) {
            try {
                Schema::table('service_categories', function (Blueprint $table) {
                    $table->index(['company_id', 'is_active'], 'idx_service_categories_company_active');
                });
            } catch (\Exception $e) {
                // Index bestaat mogelijk al
            }
        }
        
        if (in_array('sort_order', $finalColumns)) {
            try {
                Schema::table('service_categories', function (Blueprint $table) {
                    $table->index('sort_order', 'idx_service_categories_sort_order');
                });
            } catch (\Exception $e) {
                // Index bestaat mogelijk al
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign keys eerst
        try {
            Schema::table('service_categories', function (Blueprint $table) {
                $table->dropForeign(['company_id']);
            });
        } catch (\Exception $e) {
            // Foreign key bestaat mogelijk niet
        }
        
        try {
            Schema::table('service_categories', function (Blueprint $table) {
                $table->dropForeign(['created_by']);
            });
        } catch (\Exception $e) {
            // Foreign key bestaat mogelijk niet
        }
        
        try {
            Schema::table('service_categories', function (Blueprint $table) {
                $table->dropForeign(['updated_by']);
            });
        } catch (\Exception $e) {
            // Foreign key bestaat mogelijk niet
        }

        // Drop indexes
        try {
            Schema::table('service_categories', function (Blueprint $table) {
                $table->dropIndex('idx_service_categories_company_status');
            });
        } catch (\Exception $e) {
            // Index bestaat mogelijk niet
        }
        
        try {
            Schema::table('service_categories', function (Blueprint $table) {
                $table->dropIndex('idx_service_categories_company_active');
            });
        } catch (\Exception $e) {
            // Index bestaat mogelijk niet
        }
        
        try {
            Schema::table('service_categories', function (Blueprint $table) {
                $table->dropIndex('idx_service_categories_sort_order');
            });
        } catch (\Exception $e) {
            // Index bestaat mogelijk niet
        }

        // Drop kolommen die we mogelijk hebben toegevoegd
        $existingColumns = $this->getExistingColumns();
        $columnsToDrop = [];
        
        // Alleen kolommen droppen die we hebben toegevoegd (behalve company_id want die bestond al)
        $newColumns = ['status', 'icon', 'color', 'settings', 'created_by', 'updated_by', 'deleted_at'];
        
        foreach ($newColumns as $column) {
            if (in_array($column, $existingColumns)) {
                $columnsToDrop[] = $column;
            }
        }
        
        if (!empty($columnsToDrop)) {
            Schema::table('service_categories', function (Blueprint $table) use ($columnsToDrop) {
                $table->dropColumn($columnsToDrop);
            });
        }
    }
    
    /**
     * Get existing columns from the service_categories table
     */
    private function getExistingColumns(): array
    {
        try {
            $columns = DB::select('SHOW COLUMNS FROM service_categories');
            return array_column($columns, 'Field');
        } catch (\Exception $e) {
            return [];
        }
    }
};