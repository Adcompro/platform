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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, boolean, integer, json
            $table->string('group')->default('general'); // general, email, invoice, etc.
            $table->string('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();
            
            $table->index(['group', 'key']);
        });
        
        // Insert default settings
        DB::table('settings')->insert([
            [
                'key' => 'app_timezone',
                'value' => 'Europe/Amsterdam',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Application timezone for date/time display',
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'date_format',
                'value' => 'd-m-Y',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Date format for display',
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'time_format',
                'value' => 'H:i',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Time format for display',
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};