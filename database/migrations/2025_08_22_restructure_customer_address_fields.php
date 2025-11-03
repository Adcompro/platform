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
        // First, add new columns
        Schema::table('customers', function (Blueprint $table) {
            // Add new fields if they don't exist
            if (!Schema::hasColumn('customers', 'street')) {
                $table->string('street')->nullable()->after('address');
            }
            if (!Schema::hasColumn('customers', 'addition')) {
                $table->string('addition', 50)->nullable()->after('street');
            }
            
            // Keep city, zip_code, country (already exist)
            // But ensure city exists
            if (!Schema::hasColumn('customers', 'city')) {
                $table->string('city', 100)->nullable()->after('zip_code');
            }
        });

        // Then, migrate existing data to preserve it
        DB::table('customers')->get()->each(function ($customer) {
            $updates = [];
            
            // If old address field has data and new fields are empty, migrate it
            if ($customer->address && !$customer->city) {
                $addressParts = explode(',', $customer->address);
                if (count($addressParts) > 0) {
                    // Try to parse the old address
                    $updates['street'] = trim($addressParts[0]);
                    
                    if (count($addressParts) > 1) {
                        // Last part is often city
                        $lastPart = trim(end($addressParts));
                        
                        // Check if it's a postcode + city combination
                        if (preg_match('/^(\d{4}\s?[A-Z]{2})\s+(.+)$/i', $lastPart, $matches)) {
                            $updates['zip_code'] = $matches[1];
                            $updates['city'] = $matches[2];
                        } else {
                            $updates['city'] = $lastPart;
                        }
                    }
                }
            }
            
            // If street_address has data, use it for street
            if (isset($customer->street_address) && $customer->street_address && !isset($updates['street'])) {
                $updates['street'] = $customer->street_address;
            }
            
            // If address_line_2 has data, use it for addition
            if (isset($customer->address_line_2) && $customer->address_line_2) {
                $updates['addition'] = $customer->address_line_2;
            }
            
            // Update if we have data to migrate
            if (!empty($updates)) {
                DB::table('customers')
                    ->where('id', $customer->id)
                    ->update($updates);
            }
        });

        // Finally, drop old columns
        Schema::table('customers', function (Blueprint $table) {
            // Drop old fields
            if (Schema::hasColumn('customers', 'street_address')) {
                $table->dropColumn('street_address');
            }
            if (Schema::hasColumn('customers', 'address_line_2')) {
                $table->dropColumn('address_line_2');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Add back old fields
            if (!Schema::hasColumn('customers', 'street_address')) {
                $table->string('street_address')->nullable()->after('address');
            }
            if (!Schema::hasColumn('customers', 'address_line_2')) {
                $table->string('address_line_2')->nullable()->after('street_address');
            }
            
            // Drop new fields
            if (Schema::hasColumn('customers', 'street')) {
                $table->dropColumn('street');
            }
            if (Schema::hasColumn('customers', 'addition')) {
                $table->dropColumn('addition');
            }
        });
    }
};