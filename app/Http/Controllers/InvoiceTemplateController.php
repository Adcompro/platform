<?php

namespace App\Http\Controllers;

use App\Models\InvoiceTemplate;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InvoiceTemplateController extends Controller
{
    /**
     * Display a listing of invoice templates
     */
    public function index(Request $request)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can manage invoice templates.');
        }

        $query = InvoiceTemplate::query();

        // Company isolation
        if (Auth::user()->role !== 'super_admin') {
            $query->where(function($q) {
                $q->whereNull('company_id')
                  ->orWhere('company_id', Auth::user()->company_id);
            });
        }

        // Search functionality
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by type
        if ($request->filled('template_type')) {
            $query->where('template_type', $request->template_type);
        }

        $templates = $query->orderBy('is_default', 'desc')
                          ->orderBy('name')
                          ->paginate(15);

        return view('invoice-templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new template
     */
    public function create()
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can create invoice templates.');
        }

        $companies = Company::when(Auth::user()->role !== 'super_admin', function($q) {
            $q->where('id', Auth::user()->company_id);
        })->get();

        // Define available blocks for the builder
        $availableBlocks = array_values($this->getAvailableBlocks());

        return view('invoice-templates.create', compact('companies', 'availableBlocks'));
    }

    /**
     * Store a newly created template
     */
    public function store(Request $request)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can create invoice templates.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'company_id' => 'nullable|exists:companies,id',
            'template_type' => 'required|in:standard,modern,classic,minimal,detailed,custom',
            'color_scheme' => 'required|in:blue,green,red,purple,gray,indigo,yellow,custom',
            'primary_color' => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'accent_color' => 'nullable|string|max:7',
            'logo_position' => 'required|in:left,center,right,none',
            'logo_file' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'font_family' => 'required|in:Inter,Arial,Times New Roman,Helvetica,Georgia,Roboto',
            'font_size' => 'required|in:small,normal,large',
            'layout_config' => 'nullable|json',
            'is_default' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            // Generate slug
            $validated['slug'] = Str::slug($validated['name']);
            
            // Ensure unique slug
            $counter = 1;
            $originalSlug = $validated['slug'];
            while (InvoiceTemplate::where('slug', $validated['slug'])->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }

            // Set company_id for non-super_admin
            if (Auth::user()->role !== 'super_admin') {
                $validated['company_id'] = Auth::user()->company_id;
            }

            // Handle logo upload
            if ($request->hasFile('logo_file') && $request->file('logo_file')->isValid()) {
                try {
                    $logoFile = $request->file('logo_file');
                    $companyId = $validated['company_id'] ?? 'system';
                    $fileName = 'invoice_logo_' . $companyId . '_' . time() . '.' . $logoFile->getClientOriginalExtension();
                    
                    // Try alternative upload method
                    $destinationPath = storage_path('app/public/logos');
                    
                    // Ensure directory exists with proper permissions
                    if (!file_exists($destinationPath)) {
                        mkdir($destinationPath, 0755, true);
                    }
                    
                    // Move uploaded file directly
                    if ($logoFile->move($destinationPath, $fileName)) {
                        $fullPath = $destinationPath . '/' . $fileName;
                        
                        if (file_exists($fullPath)) {
                            // Save the path without 'public/' prefix for URL generation
                            $validated['logo_path'] = 'logos/' . $fileName;
                            \Log::info('Logo uploaded successfully in store: ' . $validated['logo_path'] . ' at path: ' . $fullPath);
                            
                            // Set proper permissions
                            chmod($fullPath, 0644);
                        } else {
                            \Log::error('Logo file not found after upload in store: ' . $fullPath);
                            throw new \Exception('Logo upload failed - file not saved');
                        }
                    } else {
                        \Log::error('Failed to move logo file in store: ' . $fileName);
                        throw new \Exception('Logo upload failed - could not move file');
                    }
                } catch (\Exception $e) {
                    \Log::error('Logo upload error in store: ' . $e->getMessage());
                    // Don't fail the entire create, just skip logo
                    session()->flash('warning', 'Logo upload failed: ' . $e->getMessage());
                }
            }

            // Parse layout configuration from JSON
            if ($request->filled('layout_config')) {
                $layoutConfig = json_decode($request->layout_config, true);
                
                // Set visibility flags based on layout config
                $validated['show_logo'] = $layoutConfig['show_logo'] ?? true;
                $validated['show_header'] = $layoutConfig['show_header'] ?? true;
                $validated['show_footer'] = $layoutConfig['show_footer'] ?? true;
                $validated['show_payment_terms'] = $layoutConfig['show_payment_terms'] ?? true;
                $validated['show_bank_details'] = $layoutConfig['show_bank_details'] ?? true;
                $validated['show_budget_overview'] = $layoutConfig['show_budget_overview'] ?? true;
                $validated['show_additional_costs_section'] = $layoutConfig['show_additional_costs_section'] ?? true;
                $validated['show_project_details'] = $layoutConfig['show_project_details'] ?? true;
                $validated['show_time_entry_details'] = $layoutConfig['show_time_entry_details'] ?? false;
                $validated['show_page_numbers'] = $layoutConfig['show_page_numbers'] ?? true;
                $validated['show_subtotals'] = $layoutConfig['show_subtotals'] ?? true;
                $validated['show_tax_details'] = $layoutConfig['show_tax_details'] ?? true;
                $validated['show_discount_section'] = $layoutConfig['show_discount_section'] ?? false;
                $validated['show_notes_section'] = $layoutConfig['show_notes_section'] ?? true;
                
                // Store block positions
                $validated['block_positions'] = $layoutConfig['blocks'] ?? [];
            }

            // Set blade template path
            $validated['blade_template'] = 'invoices.templates.' . $validated['slug'];
            $validated['is_active'] = true;

            // If setting as default, unset other defaults
            if ($request->boolean('is_default')) {
                InvoiceTemplate::where('company_id', $validated['company_id'] ?? null)
                    ->update(['is_default' => false]);
            }

            $template = InvoiceTemplate::create($validated);

            DB::commit();

            return redirect()->route('invoice-templates.show', $template)
                ->with('success', 'Invoice template created successfully');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating invoice template', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Error creating template: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified template
     */
    public function show(InvoiceTemplate $invoiceTemplate)
    {
        // Authorization check
        if (!$this->canViewTemplate($invoiceTemplate)) {
            abort(403, 'Access denied.');
        }

        // Get sample data for preview
        $previewData = $this->getSamplePreviewData();

        return view('invoice-templates.show', compact('invoiceTemplate', 'previewData'));
    }

    /**
     * Show the form for editing the template
     */
    public function edit(InvoiceTemplate $invoiceTemplate)
    {
        // Authorization check
        if (!$this->canEditTemplate($invoiceTemplate)) {
            abort(403, 'Access denied.');
        }

        $companies = Company::when(Auth::user()->role !== 'super_admin', function($q) {
            $q->where('id', Auth::user()->company_id);
        })->get();

        // Define available blocks for the builder
        $availableBlocksRaw = $this->getAvailableBlocks();
        $availableBlocks = array_values($availableBlocksRaw);
        
        // Parse current block positions and enrich with block names
        $currentBlocks = $invoiceTemplate->block_positions;
        if (is_string($currentBlocks)) {
            $currentBlocks = json_decode($currentBlocks, true);
        }
        if (!is_array($currentBlocks)) {
            $currentBlocks = [];
        }
        
        // Add names to current blocks from available blocks
        if ($currentBlocks) {
            foreach ($currentBlocks as &$block) {
                if (isset($block['type']) && isset($availableBlocksRaw[$block['type']])) {
                    $block['name'] = $availableBlocksRaw[$block['type']]['name'];
                    // Preserve any existing config
                    if (!isset($block['config'])) {
                        $block['config'] = [];
                    }
                }
            }
        }

        return view('invoice-templates.edit', compact('invoiceTemplate', 'companies', 'availableBlocks', 'currentBlocks'));
    }

    /**
     * Update the specified template
     */
    public function update(Request $request, InvoiceTemplate $invoiceTemplate)
    {
        // Authorization check
        if (!$this->canEditTemplate($invoiceTemplate)) {
            abort(403, 'Access denied.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'template_type' => 'required|in:standard,modern,classic,minimal,detailed,custom',
            'color_scheme' => 'required|in:blue,green,red,purple,gray,indigo,yellow,custom',
            'primary_color' => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'accent_color' => 'nullable|string|max:7',
            'logo_position' => 'required|in:left,center,right,none',
            'logo_file' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'font_family' => 'required|in:Inter,Arial,Times New Roman,Helvetica,Georgia,Roboto',
            'font_size' => 'required|in:small,normal,large',
            'layout_config' => 'nullable|json',
            'is_default' => 'boolean',
            'is_active' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            // Handle logo upload
            if ($request->hasFile('logo_file') && $request->file('logo_file')->isValid()) {
                try {
                    // Delete old logo if exists
                    if ($invoiceTemplate->logo_path && \Storage::exists('public/' . $invoiceTemplate->logo_path)) {
                        \Storage::delete('public/' . $invoiceTemplate->logo_path);
                        \Log::info('Deleted old logo file: ' . $invoiceTemplate->logo_path);
                    }
                    
                    $logoFile = $request->file('logo_file');
                    $companyId = $invoiceTemplate->company_id ?? 'system';
                    $fileName = 'invoice_logo_' . $companyId . '_' . time() . '.' . $logoFile->getClientOriginalExtension();
                    
                    // Try alternative upload method
                    $destinationPath = storage_path('app/public/logos');
                    
                    // Ensure directory exists with proper permissions
                    if (!file_exists($destinationPath)) {
                        mkdir($destinationPath, 0755, true);
                    }
                    
                    // Move uploaded file directly
                    if ($logoFile->move($destinationPath, $fileName)) {
                        $fullPath = $destinationPath . '/' . $fileName;
                        
                        if (file_exists($fullPath)) {
                            // Save the path without 'public/' prefix for URL generation
                            $validated['logo_path'] = 'logos/' . $fileName;
                            \Log::info('Logo uploaded successfully: ' . $validated['logo_path'] . ' at path: ' . $fullPath);
                            
                            // Set proper permissions
                            chmod($fullPath, 0644);
                        } else {
                            \Log::error('Logo file not found after upload: ' . $fullPath);
                            throw new \Exception('Logo upload failed - file not saved');
                        }
                    } else {
                        \Log::error('Failed to move logo file: ' . $fileName);
                        throw new \Exception('Logo upload failed - could not move file');
                    }
                } catch (\Exception $e) {
                    \Log::error('Logo upload error: ' . $e->getMessage());
                    // Don't fail the entire update, just skip logo
                    session()->flash('warning', 'Logo upload failed: ' . $e->getMessage());
                }
            } elseif ($request->has('remove_logo') && $request->remove_logo == '1') {
                // Only remove logo if explicitly requested
                if ($invoiceTemplate->logo_path && \Storage::exists('public/' . $invoiceTemplate->logo_path)) {
                    \Storage::delete('public/' . $invoiceTemplate->logo_path);
                    \Log::info('Logo removed by user request');
                }
                $validated['logo_path'] = null;
            }
            // If no new logo and no remove request, keep existing logo (don't add logo_path to $validated)

            // Parse layout configuration from JSON
            if ($request->filled('layout_config')) {
                $layoutConfig = json_decode($request->layout_config, true);
                
                // Set visibility flags based on layout config
                $validated['show_logo'] = $layoutConfig['show_logo'] ?? true;
                $validated['show_header'] = $layoutConfig['show_header'] ?? true;
                $validated['show_footer'] = $layoutConfig['show_footer'] ?? true;
                $validated['show_payment_terms'] = $layoutConfig['show_payment_terms'] ?? true;
                $validated['show_bank_details'] = $layoutConfig['show_bank_details'] ?? true;
                $validated['show_budget_overview'] = $layoutConfig['show_budget_overview'] ?? true;
                $validated['show_additional_costs_section'] = $layoutConfig['show_additional_costs_section'] ?? true;
                $validated['show_project_details'] = $layoutConfig['show_project_details'] ?? true;
                $validated['show_time_entry_details'] = $layoutConfig['show_time_entry_details'] ?? false;
                $validated['show_page_numbers'] = $layoutConfig['show_page_numbers'] ?? true;
                $validated['show_subtotals'] = $layoutConfig['show_subtotals'] ?? true;
                $validated['show_tax_details'] = $layoutConfig['show_tax_details'] ?? true;
                $validated['show_discount_section'] = $layoutConfig['show_discount_section'] ?? false;
                $validated['show_notes_section'] = $layoutConfig['show_notes_section'] ?? true;
                
                // Store block positions
                $validated['block_positions'] = $layoutConfig['blocks'] ?? [];
            }

            // If setting as default, unset other defaults
            if ($request->boolean('is_default')) {
                InvoiceTemplate::where('company_id', $invoiceTemplate->company_id)
                    ->where('id', '!=', $invoiceTemplate->id)
                    ->update(['is_default' => false]);
            }

            // Debug logging before update
            \Log::info('Before update - Current logo_path: ' . $invoiceTemplate->logo_path);
            \Log::info('Update data: ', $validated);
            
            $invoiceTemplate->update($validated);
            
            // Debug logging after update
            $invoiceTemplate->refresh();
            \Log::info('After update - New logo_path: ' . $invoiceTemplate->logo_path);

            DB::commit();

            return redirect()->route('invoice-templates.show', $invoiceTemplate)
                ->with('success', 'Invoice template updated successfully');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating invoice template', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Error updating template: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified template
     */
    public function destroy(InvoiceTemplate $invoiceTemplate)
    {
        // Authorization check
        if (!$this->canEditTemplate($invoiceTemplate)) {
            abort(403, 'Access denied.');
        }

        // Check if template is in use
        if ($invoiceTemplate->invoices()->exists() || 
            $invoiceTemplate->projects()->exists() || 
            $invoiceTemplate->customers()->exists()) {
            return back()->with('error', 'Cannot delete template that is in use');
        }

        try {
            $invoiceTemplate->delete();
            return redirect()->route('invoice-templates.index')
                ->with('success', 'Invoice template deleted successfully');
        } catch (\Exception $e) {
            Log::error('Error deleting invoice template', ['error' => $e->getMessage()]);
            return back()->with('error', 'Error deleting template: ' . $e->getMessage());
        }
    }

    /**
     * Duplicate a template
     */
    public function duplicate(InvoiceTemplate $invoiceTemplate)
    {
        // Authorization check
        if (!$this->canViewTemplate($invoiceTemplate)) {
            abort(403, 'Access denied.');
        }

        try {
            DB::beginTransaction();

            $newTemplate = $invoiceTemplate->replicate();
            $newTemplate->name = $invoiceTemplate->name . ' (Copy)';
            $newTemplate->slug = Str::slug($newTemplate->name);
            $newTemplate->is_default = false;
            
            // Ensure unique slug
            $counter = 1;
            $originalSlug = $newTemplate->slug;
            while (InvoiceTemplate::where('slug', $newTemplate->slug)->exists()) {
                $newTemplate->slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            // Set company_id for non-super_admin
            if (Auth::user()->role !== 'super_admin') {
                $newTemplate->company_id = Auth::user()->company_id;
            }

            $newTemplate->save();

            DB::commit();

            return redirect()->route('invoice-templates.edit', $newTemplate)
                ->with('success', 'Template duplicated successfully');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error duplicating invoice template', ['error' => $e->getMessage()]);
            return back()->with('error', 'Error duplicating template: ' . $e->getMessage());
        }
    }

    /**
     * Preview a new template (not yet saved)
     */
    public function previewNew(Request $request)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can preview templates.');
        }

        // Create a temporary template instance
        $template = new InvoiceTemplate();
        
        // Set values from request
        $template->name = $request->input('name', 'Untitled Template');
        $template->description = $request->input('description');
        $template->template_type = $request->input('template_type', 'standard');
        $template->color_scheme = $request->input('color_scheme', 'blue');
        $template->primary_color = $request->input('primary_color');
        $template->secondary_color = $request->input('secondary_color');
        $template->accent_color = $request->input('accent_color');
        $template->logo_position = $request->input('logo_position', 'left');
        
        // Handle logo file upload for preview
        \Log::info('Preview request files:', ['has_file' => $request->hasFile('logo_file'), 'files' => $request->allFiles()]);
        
        if ($request->hasFile('logo_file') && $request->file('logo_file')->isValid()) {
            $logoFile = $request->file('logo_file');
            // Store temporarily for preview (will be deleted after 1 hour)
            $tempFileName = 'temp_preview_' . uniqid() . '.' . $logoFile->getClientOriginalExtension();
            $logoPath = $logoFile->storeAs('public/logos/temp', $tempFileName);
            $template->logo_path = str_replace('public/', '', $logoPath);
            \Log::info('Logo uploaded for preview:', ['path' => $template->logo_path]);
        } else {
            $template->logo_path = $request->input('logo_path');
            \Log::info('No logo file uploaded, using existing:', ['path' => $template->logo_path]);
        }
        
        $template->font_family = $request->input('font_family', 'Inter');
        $template->font_size = $request->input('font_size', 'normal');
        
        // Parse layout configuration
        if ($request->filled('layout_config')) {
            $layoutConfig = json_decode($request->layout_config, true);
            
            $template->show_logo = $layoutConfig['show_logo'] ?? true;
            $template->show_header = $layoutConfig['show_header'] ?? true;
            $template->show_footer = $layoutConfig['show_footer'] ?? true;
            $template->show_payment_terms = $layoutConfig['show_payment_terms'] ?? true;
            $template->show_bank_details = $layoutConfig['show_bank_details'] ?? true;
            $template->show_budget_overview = $layoutConfig['show_budget_overview'] ?? true;
            $template->show_additional_costs_section = $layoutConfig['show_additional_costs_section'] ?? true;
            $template->show_project_details = $layoutConfig['show_project_details'] ?? true;
            $template->show_time_entry_details = $layoutConfig['show_time_entry_details'] ?? false;
            $template->show_page_numbers = $layoutConfig['show_page_numbers'] ?? true;
            $template->show_subtotals = $layoutConfig['show_subtotals'] ?? true;
            $template->show_tax_details = $layoutConfig['show_tax_details'] ?? true;
            $template->show_discount_section = $layoutConfig['show_discount_section'] ?? false;
            $template->show_notes_section = $layoutConfig['show_notes_section'] ?? true;
            
            $template->block_positions = $layoutConfig['blocks'] ?? [];
        }

        // Get sample preview data
        $previewData = $this->getSamplePreviewData();

        return view('invoice-templates.preview', [
            'invoiceTemplate' => $template,
            'previewData' => $previewData,
            'isNewTemplate' => true
        ]);
    }

    /**
     * Preview template with sample data
     */
    public function preview(Request $request, InvoiceTemplate $invoiceTemplate)
    {
        // Authorization check
        if (!$this->canViewTemplate($invoiceTemplate)) {
            abort(403, 'Access denied.');
        }

        // If this is a POST request, use the submitted data for preview
        if ($request->isMethod('post')) {
            // Create a new template instance (not clone, as clone might not work with Eloquent)
            $tempTemplate = new InvoiceTemplate();
            
            // Copy all attributes from original
            $tempTemplate->setRawAttributes($invoiceTemplate->getAttributes());
            
            // Apply submitted settings
            $tempTemplate->name = $request->input('name', $invoiceTemplate->name);
            $tempTemplate->description = $request->input('description', $invoiceTemplate->description);
            $tempTemplate->template_type = $request->input('template_type', $invoiceTemplate->template_type);
            $tempTemplate->color_scheme = $request->input('color_scheme', $invoiceTemplate->color_scheme);
            $tempTemplate->primary_color = $request->input('primary_color', $invoiceTemplate->primary_color);
            $tempTemplate->secondary_color = $request->input('secondary_color', $invoiceTemplate->secondary_color);
            $tempTemplate->accent_color = $request->input('accent_color', $invoiceTemplate->accent_color);
            $tempTemplate->logo_position = $request->input('logo_position', $invoiceTemplate->logo_position);
            $tempTemplate->font_family = $request->input('font_family', $invoiceTemplate->font_family);
            $tempTemplate->font_size = $request->input('font_size', $invoiceTemplate->font_size);
            $tempTemplate->is_active = $request->boolean('is_active');
            $tempTemplate->is_default = $request->boolean('is_default');
            
            // Check if block_positions was sent directly (from visual builder)
            if ($request->has('block_positions')) {
                $blockPositions = $request->input('block_positions');
                if (is_string($blockPositions)) {
                    $blockPositions = json_decode($blockPositions, true);
                }
                
                Log::info('Preview block_positions received directly:', [
                    'blocks_count' => count($blockPositions ?? []),
                    'blocks' => $blockPositions ?? []
                ]);
                
                $tempTemplate->block_positions = $blockPositions ?? [];
            }
            
            // Parse and apply layout configuration (if provided)
            $layoutConfig = $request->input('layout_config');
            if ($layoutConfig) {
                $layoutConfig = is_string($layoutConfig) ? json_decode($layoutConfig, true) : $layoutConfig;
                
                // Debug log to see what we're receiving
                Log::info('Preview layout config received:', [
                    'has_blocks' => isset($layoutConfig['blocks']),
                    'blocks_count' => count($layoutConfig['blocks'] ?? []),
                    'blocks' => $layoutConfig['blocks'] ?? []
                ]);
                
                // Apply boolean flags from layout config
                $tempTemplate->show_logo = $layoutConfig['show_logo'] ?? true;
                $tempTemplate->show_header = $layoutConfig['show_header'] ?? true;
                $tempTemplate->show_footer = $layoutConfig['show_footer'] ?? true;
                $tempTemplate->show_payment_terms = $layoutConfig['show_payment_terms'] ?? false;
                $tempTemplate->show_bank_details = $layoutConfig['show_bank_details'] ?? false;
                $tempTemplate->show_budget_overview = $layoutConfig['show_budget_overview'] ?? false;
                $tempTemplate->show_additional_costs_section = $layoutConfig['show_additional_costs_section'] ?? false;
                $tempTemplate->show_project_details = $layoutConfig['show_project_details'] ?? false;
                $tempTemplate->show_time_entry_details = $layoutConfig['show_time_entry_details'] ?? false;
                $tempTemplate->show_page_numbers = $layoutConfig['show_page_numbers'] ?? true;
                $tempTemplate->show_subtotals = $layoutConfig['show_subtotals'] ?? false;
                $tempTemplate->show_tax_details = $layoutConfig['show_tax_details'] ?? false;
                $tempTemplate->show_discount_section = $layoutConfig['show_discount_section'] ?? false;
                $tempTemplate->show_notes_section = $layoutConfig['show_notes_section'] ?? false;
                
                // If layout config has blocks and we haven't set block_positions yet, use them
                if (isset($layoutConfig['blocks']) && !$request->has('block_positions')) {
                    $blocks = $layoutConfig['blocks'];
                    
                    // Make sure blocks is an array
                    if (!is_array($blocks)) {
                        $blocks = [];
                    }
                    
                    // Store as JSON string
                    $tempTemplate->block_positions = $blocks;
                }
            }
            
            // If no block positions have been set at all, use empty array
            if (!isset($tempTemplate->block_positions) || empty($tempTemplate->block_positions)) {
                $tempTemplate->block_positions = [];
            }
            
            $invoiceTemplate = $tempTemplate;
        }

        $previewData = $this->getSamplePreviewData();
        
        // Always use visual builder preview
        Log::info('Using visual builder preview');
        return view('invoice-templates.preview', compact('invoiceTemplate', 'previewData'));
    }
    
    /**
     * Show preview of invoice template for AJAX modal
     */
    public function previewAjax(InvoiceTemplate $invoiceTemplate)
    {
        // Authorization check
        if (!Auth::check()) {
            return response('Unauthorized', 401);
        }
        
        $user = Auth::user();
        
        // Check permissions
        if (!in_array($user->role, ['super_admin']) && 
            !($user->role == 'admin' && $invoiceTemplate->company_id == $user->company_id) &&
            !in_array($user->role, ['project_manager', 'user', 'reader'])) {
            return response('Forbidden', 403);
        }
        
        $previewData = $this->getSamplePreviewData();
        
        // Return just the preview content without layout
        return view('invoice-templates.preview-content', compact('invoiceTemplate', 'previewData'));
    }

    /**
     * Display the help guide for invoice templates
     */
    public function help()
    {
        return view('invoice-templates.help');
    }

    /**
     * Get available blocks for the template builder
     */
    private function getAvailableBlocks()
    {
        return [
            'header' => [
                'id' => 'header',
                'name' => 'Header',
                'icon' => 'fas fa-heading',
                'description' => 'Company logo and invoice title',
                'configurable' => ['logo_position', 'show_logo', 'header_content']
            ],
            'company_info' => [
                'id' => 'company_info',
                'name' => 'Company Information',
                'icon' => 'fas fa-building',
                'description' => 'Your company details',
                'configurable' => ['show_vat', 'show_kvk', 'show_bank']
            ],
            'customer_info' => [
                'id' => 'customer_info',
                'name' => 'Customer Information',
                'icon' => 'fas fa-user',
                'description' => 'Customer billing details',
                'configurable' => ['show_contact', 'show_vat']
            ],
            'invoice_details' => [
                'id' => 'invoice_details',
                'name' => 'Invoice Details',
                'icon' => 'fas fa-file-invoice',
                'description' => 'Invoice number, date, due date',
                'configurable' => ['date_format', 'show_due_date']
            ],
            'project_info' => [
                'id' => 'project_info',
                'name' => 'Project Information',
                'icon' => 'fas fa-project-diagram',
                'description' => 'Project name and description',
                'configurable' => ['show_description', 'show_period']
            ],
            'line_items' => [
                'id' => 'line_items',
                'name' => 'Invoice Lines',
                'icon' => 'fas fa-list',
                'description' => 'Detailed invoice line items',
                'configurable' => ['show_item_codes', 'show_units', 'group_by_milestone']
            ],
            'time_entries' => [
                'id' => 'time_entries',
                'name' => 'Time Entries',
                'icon' => 'fas fa-clock',
                'description' => 'Detailed time tracking entries',
                'configurable' => ['show_user', 'show_date', 'group_by_task']
            ],
            'budget_overview' => [
                'id' => 'budget_overview',
                'name' => 'Budget Overview',
                'icon' => 'fas fa-chart-pie',
                'description' => 'Project budget status',
                'configurable' => ['show_chart', 'show_percentage']
            ],
            'additional_costs' => [
                'id' => 'additional_costs',
                'name' => 'Additional Costs',
                'icon' => 'fas fa-plus-circle',
                'description' => 'Extra charges and costs',
                'configurable' => ['group_by_category', 'show_description']
            ],
            'subtotal' => [
                'id' => 'subtotal',
                'name' => 'Subtotal',
                'icon' => 'fas fa-calculator',
                'description' => 'Subtotal calculation',
                'configurable' => []
            ],
            'tax_section' => [
                'id' => 'tax_section',
                'name' => 'Tax/VAT',
                'icon' => 'fas fa-percentage',
                'description' => 'Tax calculations',
                'configurable' => ['show_tax_number', 'tax_inclusive']
            ],
            'discount_section' => [
                'id' => 'discount_section',
                'name' => 'Discount',
                'icon' => 'fas fa-tag',
                'description' => 'Discount information',
                'configurable' => ['discount_type', 'show_reason']
            ],
            'total' => [
                'id' => 'total',
                'name' => 'Total Amount',
                'icon' => 'fas fa-euro-sign',
                'description' => 'Final total amount',
                'configurable' => ['highlight_color', 'show_in_words']
            ],
            'payment_terms' => [
                'id' => 'payment_terms',
                'name' => 'Payment Terms',
                'icon' => 'fas fa-handshake',
                'description' => 'Payment conditions and terms',
                'configurable' => ['default_terms', 'show_late_fee']
            ],
            'bank_details' => [
                'id' => 'bank_details',
                'name' => 'Bank Details',
                'icon' => 'fas fa-university',
                'description' => 'Banking information for payment',
                'configurable' => ['show_iban', 'show_bic', 'show_account_name']
            ],
            'notes' => [
                'id' => 'notes',
                'name' => 'Notes/Comments',
                'icon' => 'fas fa-comment',
                'description' => 'Additional notes or comments',
                'configurable' => ['default_text', 'show_thank_you']
            ],
            'footer' => [
                'id' => 'footer',
                'name' => 'Footer',
                'icon' => 'fas fa-align-bottom',
                'description' => 'Footer with company info',
                'configurable' => ['footer_content', 'show_page_numbers']
            ]
        ];
    }

    /**
     * Get sample data for template preview
     */
    private function getSamplePreviewData()
    {
        return [
            'invoice' => [
                'invoice_number' => 'INV-2025-001',
                'invoice_date' => now()->format('Y-m-d'),
                'due_date' => now()->addDays(30)->format('Y-m-d'),
                'status' => 'draft',
                'subtotal' => 5000.00,
                'vat_amount' => 1050.00,
                'total_amount' => 6050.00
            ],
            'company' => [
                'name' => 'Your Company Name',
                'address' => '123 Business Street',
                'city' => 'Amsterdam',
                'zip_code' => '1234 AB',
                'country' => 'Netherlands',
                'vat_number' => 'NL123456789B01',
                'kvk_number' => '12345678',
                'email' => 'info@company.com',
                'phone' => '+31 20 123 4567',
                'website' => 'www.company.com',
                'bank_name' => 'ABN AMRO',
                'iban' => 'NL00 ABNA 0123 4567 89',
                'bic' => 'ABNANL2A'
            ],
            'customer' => [
                'name' => 'Sample Customer',
                'company' => 'Customer Company B.V.',
                'address' => '456 Client Avenue',
                'city' => 'Rotterdam',
                'zip_code' => '3000 CD',
                'country' => 'Netherlands',
                'contact_person' => 'John Doe',
                'email' => 'john@customer.com'
            ],
            'project' => [
                'name' => 'Sample Project',
                'description' => 'This is a sample project for template preview'
            ],
            'line_items' => [
                [
                    'description' => 'Website Development',
                    'quantity' => 40,
                    'unit_price' => 75.00,
                    'total' => 3000.00
                ],
                [
                    'description' => 'Design Services',
                    'quantity' => 20,
                    'unit_price' => 100.00,
                    'total' => 2000.00
                ]
            ]
        ];
    }

    /**
     * Check if user can view template
     */
    private function canViewTemplate(InvoiceTemplate $template)
    {
        if (Auth::user()->role === 'super_admin') {
            return true;
        }

        if (Auth::user()->role === 'admin') {
            return $template->company_id === null || 
                   $template->company_id === Auth::user()->company_id;
        }

        return false;
    }

    /**
     * Check if user can edit template
     */
    private function canEditTemplate(InvoiceTemplate $template)
    {
        if (Auth::user()->role === 'super_admin') {
            return true;
        }

        if (Auth::user()->role === 'admin') {
            return $template->company_id === Auth::user()->company_id;
        }

        return false;
    }
}