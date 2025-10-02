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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects');
            $table->foreignId('invoicing_company_id')->constrained('companies');
            $table->foreignId('customer_id')->constrained('customers');
            $table->string('invoice_number')->nullable();
            $table->enum('status', ['draft', 'finalized', 'sent', 'paid', 'overdue', 'cancelled'])->default('draft');
            $table->boolean('is_editable')->default(true);
            $table->string('draft_name')->nullable();
            $table->text('notes')->nullable();
            $table->date('invoice_date');
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('subtotal_ex_vat', 12, 2)->default(0);
            $table->decimal('vat_amount', 12, 2)->default(0);
            $table->decimal('total_inc_vat', 12, 2)->default(0);
            $table->foreignId('finalized_by')->nullable()->constrained('users');
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['project_id', 'status']);
            $table->index(['invoicing_company_id', 'status']);
            $table->index(['customer_id', 'invoice_date']);
            $table->index('invoice_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};