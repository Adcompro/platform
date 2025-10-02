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
        Schema::table('time_entries', function (Blueprint $table) {
            // Voeg minutes veld toe als het niet bestaat
            if (!Schema::hasColumn('time_entries', 'minutes')) {
                $table->integer('minutes')->after('hours')->comment('Aantal minuten gewerkt (in stappen van 5)');
            }
            
            // Voeg entry_date toe (hernoem date naar entry_date voor consistentie)
            if (!Schema::hasColumn('time_entries', 'entry_date') && Schema::hasColumn('time_entries', 'date')) {
                $table->renameColumn('date', 'entry_date');
            }
            
            // Voeg service identificatie velden toe
            if (!Schema::hasColumn('time_entries', 'is_service_item')) {
                $table->boolean('is_service_item')->default(false)->after('is_invoiced');
            }
            
            if (!Schema::hasColumn('time_entries', 'original_service_id')) {
                $table->unsignedBigInteger('original_service_id')->nullable()->after('is_service_item');
            }
            
            // Voeg created_by en updated_by toe als ze niet bestaan
            if (!Schema::hasColumn('time_entries', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('users')->after('original_service_id');
            }
            
            if (!Schema::hasColumn('time_entries', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->constrained('users')->after('created_by');
            }
            
            // Voeg soft deletes toe als het niet bestaat
            if (!Schema::hasColumn('time_entries', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('time_entries', function (Blueprint $table) {
            // Verwijder toegevoegde kolommen
            $table->dropColumn(['minutes', 'is_service_item', 'original_service_id']);
            
            // Hernoem entry_date terug naar date
            if (Schema::hasColumn('time_entries', 'entry_date')) {
                $table->renameColumn('entry_date', 'date');
            }
            
            // Verwijder foreign keys eerst
            if (Schema::hasColumn('time_entries', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
            
            if (Schema::hasColumn('time_entries', 'updated_by')) {
                $table->dropForeign(['updated_by']);
                $table->dropColumn('updated_by');
            }
            
            // Verwijder soft deletes
            if (Schema::hasColumn('time_entries', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};