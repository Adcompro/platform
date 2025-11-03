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
        Schema::table('companies', function (Blueprint $table) {
            // Check welke kolommen er al zijn en voeg alleen ontbrekende toe
            if (!Schema::hasColumn('companies', 'legal_name')) {
                $table->string('legal_name')->nullable()->after('name');
            }
            
            if (!Schema::hasColumn('companies', 'registration_number')) {
                $table->string('registration_number')->nullable()->after('vat_number');
            }
            
            if (!Schema::hasColumn('companies', 'postal_code')) {
                $table->string('postal_code')->nullable()->after('address');
            }
            
            if (!Schema::hasColumn('companies', 'city')) {
                $table->string('city')->nullable()->after('postal_code');
            }
            
            if (!Schema::hasColumn('companies', 'country')) {
                $table->string('country')->default('Netherlands')->after('city');
            }
            
            // Financial settings
            if (!Schema::hasColumn('companies', 'default_fixed_price')) {
                $table->decimal('default_fixed_price', 10, 2)->nullable()->after('default_hourly_rate');
            }
            
            if (!Schema::hasColumn('companies', 'invoice_prefix')) {
                $table->string('invoice_prefix', 10)->nullable()->after('default_fixed_price');
            }
            
            if (!Schema::hasColumn('companies', 'next_invoice_number')) {
                $table->integer('next_invoice_number')->default(1)->after('invoice_prefix');
            }
            
            // Status kolom (naast bestaande is_active voor backwards compatibility)
            if (!Schema::hasColumn('companies', 'status')) {
                $table->enum('status', ['active', 'inactive'])->default('active')->after('is_active');
            }
            
            if (!Schema::hasColumn('companies', 'notes')) {
                $table->text('notes')->nullable()->after('status');
            }
            
            // Audit fields
            if (!Schema::hasColumn('companies', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('notes');
            }
            
            if (!Schema::hasColumn('companies', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            }
            
            // Soft deletes
            if (!Schema::hasColumn('companies', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });

        // Voeg indexes toe alleen als ze nog niet bestaan
        $this->addIndexIfNotExists('companies', 'status', 'companies_status_index');
        // Skip is_main_invoicing index omdat die al bestaat
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // Drop alleen kolommen die we hebben toegevoegd
            $columnsToCheck = [
                'legal_name',
                'registration_number', 
                'postal_code',
                'city',
                'country',
                'default_fixed_price',
                'invoice_prefix',
                'next_invoice_number',
                'status',
                'notes',
                'created_by',
                'updated_by',
                'deleted_at'
            ];
            
            foreach ($columnsToCheck as $column) {
                if (Schema::hasColumn('companies', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
        
        // Drop alleen de index die we hebben toegevoegd
        $this->dropIndexIfExists('companies', 'companies_status_index');
    }
    
    /**
     * Helper method om veilig indexes toe te voegen
     */
    private function addIndexIfNotExists($table, $column, $indexName)
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = '{$indexName}'");
        
        if (empty($indexes)) {
            Schema::table($table, function (Blueprint $table) use ($column) {
                $table->index($column);
            });
        }
    }
    
    /**
     * Helper method om veilig indexes te verwijderen
     */
    private function dropIndexIfExists($table, $indexName)
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = '{$indexName}'");
        
        if (!empty($indexes)) {
            Schema::table($table, function (Blueprint $table) use ($indexName) {
                $table->dropIndex($indexName);
            });
        }
    }
};