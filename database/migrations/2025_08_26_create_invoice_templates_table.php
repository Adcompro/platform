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
        Schema::create('invoice_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            
            // Template settings
            $table->string('template_type')->default('standard'); // standard, modern, classic, minimal
            $table->string('color_scheme')->default('blue'); // blue, green, red, purple, gray
            $table->string('logo_position')->default('left'); // left, center, right, none
            $table->boolean('show_logo')->default(true);
            $table->boolean('show_payment_terms')->default(true);
            $table->boolean('show_bank_details')->default(true);
            $table->boolean('show_budget_overview')->default(true);
            $table->boolean('show_additional_costs_section')->default(true);
            $table->boolean('show_project_details')->default(true);
            $table->boolean('show_time_entry_details')->default(false);
            $table->boolean('show_page_numbers')->default(true);
            $table->boolean('show_footer')->default(true);
            
            // Header/Footer content
            $table->text('header_content')->nullable();
            $table->text('footer_content')->nullable();
            $table->text('payment_terms_text')->nullable();
            
            // Font and spacing
            $table->string('font_family')->default('Inter');
            $table->string('font_size')->default('normal'); // small, normal, large
            $table->string('line_spacing')->default('normal'); // compact, normal, relaxed
            
            // Custom CSS
            $table->text('custom_css')->nullable();
            
            // Layout file (blade template name)
            $table->string('blade_template')->default('invoices.templates.standard');
            
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            
            $table->index(['company_id', 'is_active']);
        });
        
        // Add template columns to projects and customers
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('invoice_template_id')->nullable()->after('vat_rate')
                  ->constrained('invoice_templates')->onDelete('set null');
        });
        
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('invoice_template_id')->nullable()->after('notes')
                  ->constrained('invoice_templates')->onDelete('set null');
        });
        
        // Add template to invoices
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('invoice_template_id')->nullable()->after('invoicing_company_id')
                  ->constrained('invoice_templates')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['invoice_template_id']);
            $table->dropColumn('invoice_template_id');
        });
        
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['invoice_template_id']);
            $table->dropColumn('invoice_template_id');
        });
        
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['invoice_template_id']);
            $table->dropColumn('invoice_template_id');
        });
        
        Schema::dropIfExists('invoice_templates');
    }
};