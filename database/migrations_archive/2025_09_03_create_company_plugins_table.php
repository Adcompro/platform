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
        if (!Schema::hasTable('company_plugins')) {
            Schema::create('company_plugins', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
                $table->foreignId('plugin_id')->constrained('plugins')->onDelete('cascade');
                $table->boolean('is_enabled')->default(true);
                $table->json('settings')->nullable();
                $table->timestamps();
                
                $table->unique(['company_id', 'plugin_id']);
                $table->index('is_enabled');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_plugins');
    }
};