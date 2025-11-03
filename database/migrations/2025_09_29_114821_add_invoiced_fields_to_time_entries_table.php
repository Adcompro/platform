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
            // Gefactureerde gegevens (kunnen afwijken van origineel)
            $table->decimal('invoiced_hours', 8, 2)->nullable()->comment('Hours as invoiced (may differ from actual hours)');
            $table->decimal('invoiced_rate', 8, 2)->nullable()->comment('Hourly rate as invoiced (may differ from hourly_rate_used)');
            $table->text('invoiced_description')->nullable()->comment('Description as shown on invoice (may differ from description)');
            $table->datetime('invoiced_modified_at')->nullable()->comment('When invoiced data was last modified');
            $table->bigInteger('invoiced_modified_by')->unsigned()->nullable()->comment('User who last modified invoiced data');

            // Foreign key voor modified_by
            $table->foreign('invoiced_modified_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('time_entries', function (Blueprint $table) {
            $table->dropForeign(['invoiced_modified_by']);
            $table->dropColumn([
                'invoiced_hours',
                'invoiced_rate',
                'invoiced_description',
                'invoiced_modified_at',
                'invoiced_modified_by'
            ]);
        });
    }
};
