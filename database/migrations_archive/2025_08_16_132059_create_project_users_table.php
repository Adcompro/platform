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
        Schema::create('project_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('role_override', ['project_manager', 'user', 'reader'])->nullable();
            $table->boolean('can_edit_fee')->default(false);
            $table->boolean('can_view_financials')->default(false);
            $table->foreignId('added_by')->constrained('users');
            $table->timestamp('added_at');
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['project_id', 'user_id']);
            
            // Indexes
            $table->index(['project_id', 'role_override']);
            $table->index('added_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_users');
    }
};