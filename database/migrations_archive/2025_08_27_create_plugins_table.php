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
        Schema::create('plugins', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->string('icon')->nullable(); // Font Awesome icon class
            $table->string('category')->default('general'); // core, financial, productivity, reporting, integration
            $table->boolean('is_active')->default(false);
            $table->boolean('is_core')->default(false); // Core plugins kunnen niet uitgeschakeld worden
            $table->json('dependencies')->nullable(); // Array van plugin names die nodig zijn
            $table->json('routes')->nullable(); // Routes die bij deze plugin horen
            $table->json('permissions')->nullable(); // Welke rollen toegang hebben
            $table->json('settings')->nullable(); // Plugin-specifieke settings
            $table->integer('sort_order')->default(0);
            $table->string('version')->default('1.0.0');
            $table->string('author')->nullable();
            $table->string('url')->nullable(); // Plugin documentatie URL
            $table->timestamps();
            
            $table->index('is_active');
            $table->index('category');
            $table->index('sort_order');
        });
        
        // Plugin activatie log voor audit trail
        Schema::create('plugin_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plugin_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained();
            $table->enum('action', ['activated', 'deactivated', 'configured', 'installed', 'uninstalled']);
            $table->json('old_settings')->nullable();
            $table->json('new_settings')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at');
            
            $table->index(['plugin_id', 'created_at']);
            $table->index('user_id');
        });
        
        // Company-specific plugin configuratie
        Schema::create('company_plugins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('plugin_id')->constrained()->onDelete('cascade');
            $table->boolean('is_enabled')->default(true);
            $table->json('settings')->nullable(); // Company-specifieke plugin settings
            $table->timestamps();
            
            $table->unique(['company_id', 'plugin_id']);
            $table->index('is_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_plugins');
        Schema::dropIfExists('plugin_activities');
        Schema::dropIfExists('plugins');
    }
};