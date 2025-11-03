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
        // Migrate existing company_id to customer_companies pivot table
        $customers = DB::table('customers')
            ->whereNotNull('company_id')
            ->get();
            
        foreach ($customers as $customer) {
            DB::table('customer_companies')->insert([
                'customer_id' => $customer->id,
                'company_id' => $customer->company_id,
                'is_primary' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        // Note: We're keeping the company_id column for now for backwards compatibility
        // It can be removed in a future migration after ensuring everything works
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Clear the pivot table
        DB::table('customer_companies')->truncate();
    }
};