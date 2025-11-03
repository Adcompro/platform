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
        Schema::table('customers', function (Blueprint $table) {
            // Check en voeg nieuwe velden toe indien ze nog niet bestaan
            if (!Schema::hasColumn('customers', 'street_address')) {
                $table->string('street_address')->nullable()->after('address');
            }
            if (!Schema::hasColumn('customers', 'address_line_2')) {
                $table->string('address_line_2')->nullable()->after('street_address');
            }
            if (!Schema::hasColumn('customers', 'zip_code')) {
                $table->string('zip_code', 20)->nullable()->after('address_line_2');
            }
            if (!Schema::hasColumn('customers', 'city')) {
                $table->string('city', 100)->nullable()->after('zip_code');
            }
            if (!Schema::hasColumn('customers', 'country')) {
                $table->string('country', 100)->nullable()->default('Netherlands')->after('city');
            }
            
            // We behouden het oude address veld voorlopig voor backward compatibility
            // Dit kan later verwijderd worden na data migratie
        });
        
        // Migreer bestaande address data naar nieuwe velden indien mogelijk
        $customers = DB::table('customers')->whereNotNull('address')->get();
        foreach ($customers as $customer) {
            // Simpele migratie - zet alles in street_address
            // In productie zou je hier een meer geavanceerde parsing kunnen doen
            DB::table('customers')
                ->where('id', $customer->id)
                ->update(['street_address' => $customer->address]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'street_address',
                'address_line_2', 
                'zip_code',
                'city',
                'country'
            ]);
        });
    }
};