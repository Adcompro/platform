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
            // Add new detailed address fields after 'address' field
            if (!Schema::hasColumn('companies', 'street')) {
                $table->string('street')->nullable()->after('address');
            }
            if (!Schema::hasColumn('companies', 'house_number')) {
                $table->string('house_number', 20)->nullable()->after('street');
            }
            if (!Schema::hasColumn('companies', 'addition')) {
                $table->string('addition', 20)->nullable()->after('house_number');
            }
            // zip_code already exists as postal_code
            // city already exists
            // country already exists
        });

        // Migrate existing data from 'address' field to new fields
        $companies = DB::table('companies')->whereNotNull('address')->get();
        
        foreach ($companies as $company) {
            $updates = [];
            
            // Try to parse the existing address
            if ($company->address) {
                $lines = explode("\n", $company->address);
                if (count($lines) > 0) {
                    // First line is usually street + number
                    $firstLine = trim($lines[0]);
                    
                    // Try to extract house number from street
                    if (preg_match('/^(.+?)\s+(\d+[a-zA-Z]?)(?:\s*-?\s*([a-zA-Z0-9]+))?$/', $firstLine, $matches)) {
                        $updates['street'] = trim($matches[1]);
                        $updates['house_number'] = $matches[2];
                        if (isset($matches[3]) && $matches[3]) {
                            $updates['addition'] = $matches[3];
                        }
                    } else {
                        // If we can't parse it, just put it all in street
                        $updates['street'] = $firstLine;
                    }
                }
                
                // Update the company record
                if (!empty($updates)) {
                    DB::table('companies')
                        ->where('id', $company->id)
                        ->update($updates);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // Consolidate back to address field before dropping columns
            $companies = DB::table('companies')
                ->whereNotNull('street')
                ->get();
            
            foreach ($companies as $company) {
                $address = $company->street;
                if ($company->house_number) {
                    $address .= ' ' . $company->house_number;
                }
                if ($company->addition) {
                    $address .= '-' . $company->addition;
                }
                if ($company->postal_code) {
                    $address .= "\n" . $company->postal_code;
                }
                if ($company->city) {
                    $address .= ' ' . $company->city;
                }
                
                DB::table('companies')
                    ->where('id', $company->id)
                    ->update(['address' => $address]);
            }
        });
        
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['street', 'house_number', 'addition']);
        });
    }
};