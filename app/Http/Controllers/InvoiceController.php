<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\InvoiceDraftAction;
use App\Models\Project;
use App\Models\Company;
use App\Models\Customer;
use App\Models\TimeEntry;
use App\Models\ProjectAdditionalCost;
use App\Services\InvoiceGenerationService;

class InvoiceController extends Controller
{
    /**
     * Display listing of invoices
     */
    public function index(Request $request)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators and project managers can view invoices.');
        }

        $user = Auth::user();
        $query = Invoice::with(['project', 'customer', 'invoicingCompany']);

        // Filter by company for non-super admins
        if ($user->role !== 'super_admin') {
            $query->where('invoicing_company_id', $user->company_id);
        }

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        
        if ($request->filled('period')) {
            switch ($request->period) {
                case 'this_month':
                    $query->whereMonth('invoice_date', now()->month)
                          ->whereYear('invoice_date', now()->year);
                    break;
                case 'last_month':
                    $query->whereMonth('invoice_date', now()->subMonth()->month)
                          ->whereYear('invoice_date', now()->subMonth()->year);
                    break;
                case 'this_quarter':
                    $quarter = ceil(now()->month / 3);
                    $query->whereRaw('QUARTER(invoice_date) = ?', [$quarter])
                          ->whereYear('invoice_date', now()->year);
                    break;
                case 'this_year':
                    $query->whereYear('invoice_date', now()->year);
                    break;
            }
        }

        $invoices = $query->orderBy('invoice_date', 'desc')
                         ->orderBy('created_at', 'desc')
                         ->paginate(20);
        
        // Get filter options
        $projects = Project::when($user->role !== 'super_admin', function($q) use ($user) {
                        $q->where('company_id', $user->company_id);
                    })
                    ->orderBy('name')
                    ->get();
                    
        $customers = Customer::when($user->role !== 'super_admin', function($q) use ($user) {
                        $q->whereHas('projects', function($q2) use ($user) {
                            $q2->where('company_id', $user->company_id);
                        });
                    })
                    ->orderBy('name')
                    ->get();

        // Calculate statistics
        $stats = [
            'draft_count' => Invoice::where('status', 'draft')
                ->when($user->role !== 'super_admin', function($q) use ($user) {
                    $q->where('invoicing_company_id', $user->company_id);
                })->count(),
            
            'draft_total' => Invoice::where('status', 'draft')
                ->when($user->role !== 'super_admin', function($q) use ($user) {
                    $q->where('invoicing_company_id', $user->company_id);
                })->sum('total_amount'),
                
            'finalized_count' => Invoice::where('status', 'finalized')
                ->when($user->role !== 'super_admin', function($q) use ($user) {
                    $q->where('invoicing_company_id', $user->company_id);
                })->count(),
            
            'finalized_total' => Invoice::where('status', 'finalized')
                ->when($user->role !== 'super_admin', function($q) use ($user) {
                    $q->where('invoicing_company_id', $user->company_id);
                })->sum('total_amount'),
                
            'sent_count' => Invoice::where('status', 'sent')
                ->when($user->role !== 'super_admin', function($q) use ($user) {
                    $q->where('invoicing_company_id', $user->company_id);
                })->count(),
            
            'sent_total' => Invoice::where('status', 'sent')
                ->when($user->role !== 'super_admin', function($q) use ($user) {
                    $q->where('invoicing_company_id', $user->company_id);
                })->sum('total_amount'),
                
            'paid_count' => Invoice::where('status', 'paid')
                ->when($user->role !== 'super_admin', function($q) use ($user) {
                    $q->where('invoicing_company_id', $user->company_id);
                })->count(),
            
            'paid_total' => Invoice::where('status', 'paid')
                ->when($user->role !== 'super_admin', function($q) use ($user) {
                    $q->where('invoicing_company_id', $user->company_id);
                })->sum('total_amount'),
        ];

        return view('invoices.index', compact('invoices', 'projects', 'customers', 'stats'));
    }

    /**
     * Show form to create new invoice
     */
    public function create(Request $request)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can create invoices.');
        }

        $user = Auth::user();
        // Plugin system removed - always show companies
        $isCompaniesPluginActive = true;
        
        // Get companies based on plugin status
        $companies = collect();
        $defaultCompany = null;
        
        if ($isCompaniesPluginActive) {
            // Multi-company mode: show all companies
            $companies = Company::when($user->role !== 'super_admin', function($q) use ($user) {
                    $q->where('id', $user->company_id);
                })
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        } else {
            // Single company mode: auto-select the only company
            $defaultCompany = Company::when($user->role !== 'super_admin', function($q) use ($user) {
                    $q->where('id', $user->company_id);
                })
                ->where('is_active', true)
                ->first();
        }
        
        // Get customers
        $customers = Customer::when($user->role !== 'super_admin', function($q) use ($user) {
                        $q->where('company_id', $user->company_id);
                    })
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get();
        
        // Get projects
        $projects = Project::when($user->role !== 'super_admin', function($q) use ($user) {
                        $q->where('company_id', $user->company_id);
                    })
                    ->with(['customer', 'milestones.tasks'])
                    ->where('status', 'active')
                    ->orderBy('name')
                    ->get();
        
        // Pre-select project if provided
        $selectedProject = null;
        if ($request->has('project_id')) {
            $selectedProject = Project::find($request->project_id);
        }
        
        // Default period (current month)
        $periodStart = now()->startOfMonth();
        $periodEnd = now()->endOfMonth();
        
        return view('invoices.create', compact(
            'projects', 
            'customers', 
            'companies', 
            'selectedProject', 
            'periodStart', 
            'periodEnd',
            'isCompaniesPluginActive',
            'defaultCompany'
        ));
    }

    /**
     * Generate invoice for project
     */
    public function generate(Request $request)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can generate invoices.');
        }

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
        ]);

        try {
            DB::beginTransaction();

            $project = Project::findOrFail($validated['project_id']);
            
            // Check if invoice already exists for this period
            $existingInvoice = Invoice::where('project_id', $project->id)
                ->where('period_start', $validated['period_start'])
                ->where('period_end', $validated['period_end'])
                ->where('status', '!=', 'cancelled')
                ->first();
            
            if ($existingInvoice) {
                return redirect()->route('invoices.edit', $existingInvoice)
                    ->with('info', 'An invoice already exists for this period. You can edit it here.');
            }
            
            // Generate invoice using service
            $service = new InvoiceGenerationService();
            $invoice = $service->generateForProject(
                $project,
                Carbon::parse($validated['period_start']),
                Carbon::parse($validated['period_end'])
            );

            DB::commit();

            Log::info('Invoice generated successfully', [
                'invoice_id' => $invoice->id,
                'project_id' => $project->id,
                'period' => $validated['period_start'] . ' to ' . $validated['period_end'],
                'user_id' => Auth::id()
            ]);

            return redirect()->route('invoices.edit', $invoice)
                ->with('success', 'Invoice draft generated successfully. You can now edit it before finalizing.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to generate invoice', [
                'error' => $e->getMessage(),
                'project_id' => $validated['project_id'],
                'user_id' => Auth::id()
            ]);

            return back()->withErrors(['error' => 'Failed to generate invoice: ' . $e->getMessage()])
                        ->withInput();
        }
    }

    /**
     * Display help guide for invoices
     */
    public function help()
    {
        return view('invoices.help');
    }

    /**
     * Show invoice details
     */
    public function show(Invoice $invoice)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied.');
        }

        // Check company access
        if (Auth::user()->role !== 'super_admin' && $invoice->invoicing_company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied.');
        }

        $invoice->load([
            'project.customer',
            'invoicingCompany',
            'lines' => function($q) {
                $q->orderBy('sort_order');
            },
            'creator',
            'approvedBy'
        ]);

        return view('invoices.show', compact('invoice'));
    }

    /**
     * Show form to edit invoice
     */
    public function edit(Invoice $invoice)
    {
        // Authorization check
        if (!Auth::check() || !in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can edit invoices.');
        }

        // Check if invoice is editable
        if ($invoice->status !== 'draft') {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Only draft invoices can be edited.');
        }

        // Check company access
        if (Auth::user()->role !== 'super_admin' && $invoice->invoicing_company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied.');
        }

        $invoice->load([
            'project.customer',
            'project.milestones.tasks',
            'invoicingCompany',
            'lines' => function($q) {
                $q->orderBy('sort_order');
            }
        ]);

        // Get available time entries not yet invoiced
        $availableTimeEntries = TimeEntry::where('project_id', $invoice->project_id)
            ->where('status', 'approved')
            ->whereNull('invoice_id')
            ->whereBetween('entry_date', [$invoice->period_start, $invoice->period_end])
            ->with(['user', 'milestone', 'task'])
            ->get();

        // Get available additional costs
        $availableCosts = ProjectAdditionalCost::where('project_id', $invoice->project_id)
            ->where('is_active', true)
            ->where(function($q) use ($invoice) {
                $q->where('cost_type', 'one_time')
                  ->whereBetween('start_date', [$invoice->period_start, $invoice->period_end])
                  ->orWhere(function($q2) use ($invoice) {
                      $q2->where('cost_type', 'monthly_recurring')
                         ->where('start_date', '<=', $invoice->period_end)
                         ->where(function($q3) use ($invoice) {
                             $q3->whereNull('end_date')
                                ->orWhere('end_date', '>=', $invoice->period_start);
                         });
                  });
            })
            ->get();

        // Plugin system removed - always show companies
        $isCompaniesPluginActive = true;
        
        // Get companies based on plugin status
        $companies = collect();
        $defaultCompany = null;
        
        if ($isCompaniesPluginActive) {
            // Multi-company mode: show available companies
            $companies = Company::where('is_active', true)
                ->when(Auth::user()->role !== 'super_admin', function($q) {
                    $q->where('id', Auth::user()->company_id);
                })
                ->orderBy('name')
                ->get();
        } else {
            // Single company mode: auto-select the current company
            $defaultCompany = Company::where('is_active', true)
                ->when(Auth::user()->role !== 'super_admin', function($q) {
                    $q->where('id', Auth::user()->company_id);
                })
                ->first();
        }

        // Get customers for customer selection
        $customers = Customer::where('is_active', true)
            ->when(Auth::user()->role !== 'super_admin', function($q) {
                $q->where('company_id', Auth::user()->company_id);
            })
            ->orderBy('name')
            ->get();

        // Get projects for project selection
        $projects = Project::where('status', 'active')
            ->when(Auth::user()->role !== 'super_admin', function($q) {
                $q->where('company_id', Auth::user()->company_id);
            })
            ->orderBy('name')
            ->get();

        // Get available invoice templates
        $templates = \App\Models\InvoiceTemplate::where('is_active', true)
            ->when(Auth::user()->role !== 'super_admin', function($q) {
                $q->where(function($query) {
                    $query->whereNull('company_id')
                          ->orWhere('company_id', Auth::user()->company_id);
                });
            })
            ->orderBy('name')
            ->get();

        return view('invoices.edit', compact(
            'invoice', 
            'availableTimeEntries', 
            'availableCosts', 
            'companies', 
            'customers', 
            'projects', 
            'templates',
            'isCompaniesPluginActive',
            'defaultCompany'
        ));
    }

    /**
     * Update invoice
     */
    public function update(Request $request, Invoice $invoice)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied.');
        }

        // Check if invoice is editable
        if ($invoice->status !== 'draft') {
            return back()->with('error', 'Only draft invoices can be edited.');
        }


        $validated = $request->validate([
            'invoice_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'invoice_template_id' => 'nullable|exists:invoice_templates,id',
            'notes' => 'nullable|string',
            'internal_notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $invoice->update($validated);


            // Handle line items updates
            if ($request->has('lines')) {
                $processedLines = [];
                
                foreach ($request->lines as $lineId => $lineData) {
                    // Check if this is a new merged line
                    if (str_starts_with($lineId, 'merged_')) {
                        // Handle new merged line
                        $quantity = $lineData['quantity'] ?? 0;
                        $unitPrice = $lineData['unit_price'] ?? 0;
                        $vatRate = $lineData['vat_rate'] ?? 21;
                        $lineTotal = $quantity * $unitPrice;
                        $vatAmount = $lineTotal * ($vatRate / 100);
                        
                        // Create source data from merged lines and collect time entry IDs
                        $sourceData = [];
                        $timeEntryIds = [];
                        if (isset($lineData['merged_from'])) {
                            $mergedIds = explode(',', $lineData['merged_from']);
                            foreach ($mergedIds as $mergedId) {
                                $sourceLine = InvoiceLine::find($mergedId);
                                if ($sourceLine) {
                                    $sourceData[] = [
                                        'id' => $sourceLine->id,
                                        'description' => $sourceLine->description,
                                        'quantity' => $sourceLine->quantity,
                                        'unit_price' => $sourceLine->unit_price,
                                        'amount' => $sourceLine->amount,
                                    ];
                                    
                                    // Collect time entry IDs linked to this line
                                    $timeEntryIds = array_merge(
                                        $timeEntryIds, 
                                        $sourceLine->timeEntries()->pluck('id')->toArray()
                                    );
                                    
                                    // Don't delete yet, wait until after we create the new line
                                }
                            }
                        }
                        
                        // Create new merged line
                        $newLine = InvoiceLine::create([
                            'invoice_id' => $invoice->id,
                            'line_type' => 'custom',
                            'source_type' => 'merged',
                            'description' => $lineData['description'],
                            'quantity' => $quantity,
                            'unit' => 'hours',
                            'unit_price' => $unitPrice,
                            'unit_price_ex_vat' => $unitPrice,
                            'amount' => $lineTotal,
                            'line_total_ex_vat' => $lineTotal,
                            'vat_rate' => $vatRate,
                            'line_vat_amount' => $vatAmount,
                            'is_billable' => true,
                            'is_merged_line' => true,
                            'source_data' => $sourceData,
                            'defer_to_next_month' => (bool)($lineData['defer_to_next_month'] ?? false),
                        ]);
                        
                        $processedLines[] = $newLine->id;
                        
                        // Update time entries to point to the new merged line
                        if (!empty($timeEntryIds)) {
                            TimeEntry::whereIn('id', $timeEntryIds)->update([
                                'invoice_line_id' => $newLine->id,
                                // Keep them marked as invoiced
                                'is_invoiced' => true,
                                'invoice_id' => $invoice->id
                            ]);
                        }
                        
                        // Now delete the original lines after time entries have been moved
                        if (isset($lineData['merged_from'])) {
                            $mergedIds = explode(',', $lineData['merged_from']);
                            foreach ($mergedIds as $mergedId) {
                                $sourceLine = InvoiceLine::find($mergedId);
                                if ($sourceLine) {
                                    $sourceLine->delete();
                                }
                            }
                        }
                        
                        // Track action
                        InvoiceDraftAction::create([
                            'invoice_id' => $invoice->id,
                            'user_id' => Auth::id(),
                            'action' => 'line_merged',
                            'details' => [
                                'merged_from' => $lineData['merged_from'] ?? '',
                                'new_line_id' => $newLine->id,
                                'description' => $lineData['description'],
                                'time_entries_moved' => count($timeEntryIds),
                            ]
                        ]);
                        
                    } else {
                        // Handle existing line update
                        $line = InvoiceLine::find($lineId);
                        if ($line && $line->invoice_id === $invoice->id) {
                            $quantity = $lineData['quantity'] ?? $line->quantity;
                            $unitPrice = $lineData['unit_price'] ?? $line->unit_price;
                            $vatRate = $lineData['vat_rate'] ?? $line->vat_rate ?? 21;
                            $lineTotal = $quantity * $unitPrice;
                            $vatAmount = $lineTotal * ($vatRate / 100);
                            
                            $line->update([
                                'description' => $lineData['description'] ?? $line->description,
                                'quantity' => $quantity,
                                'unit_price' => $unitPrice,
                                'unit_price_ex_vat' => $unitPrice,
                                'amount' => $lineTotal,
                                'line_total_ex_vat' => $lineTotal,
                                'vat_rate' => $vatRate,
                                'line_vat_amount' => $vatAmount,
                                'defer_to_next_month' => (bool)($lineData['defer_to_next_month'] ?? false),
                            ]);

                            // Update linked time entries with invoiced data for any line that has time entries
                            $this->updateLinkedTimeEntries($line, [
                                'invoiced_hours' => $quantity,
                                'invoiced_rate' => $unitPrice,
                                'invoiced_description' => $lineData['description'] ?? $line->description,
                                'invoiced_modified_at' => now(),
                                'invoiced_modified_by' => Auth::id(),
                            ]);

                            $processedLines[] = $line->id;
                        }
                    }
                }
            }

            // Recalculate totals
            $this->recalculateInvoiceTotals($invoice);

            // Check if user clicked "Save & Finalize"
            if ($request->get('action') === 'finalize') {
                // Continue with finalize logic in the same transaction
                $this->finalizeInvoiceInTransaction($invoice);
                DB::commit();

                return redirect()->route('invoices.show', $invoice)
                    ->with('success', 'Invoice updated and finalized successfully.');
            }

            DB::commit();

            return redirect()->route('invoices.edit', $invoice)
                ->with('success', 'Invoice updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to update invoice', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoice->id
            ]);

            return back()->withErrors(['error' => 'Failed to update invoice: ' . $e->getMessage()])
                        ->withInput();
        }
    }

    /**
     * Finalize invoice
     */
    public function finalize(Request $request, Invoice $invoice)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied.');
        }

        // Check if invoice can be finalized
        if ($invoice->status !== 'draft') {
            return back()->with('error', 'Only draft invoices can be finalized.');
        }

        if ($invoice->lines->count() === 0) {
            return back()->with('error', 'Cannot finalize invoice without line items.');
        }

        try {
            DB::beginTransaction();

            // Generate final invoice number if not set
            if (!$invoice->invoice_number || str_starts_with($invoice->invoice_number, 'DRAFT-')) {
                $invoice->invoice_number = $this->generateFinalInvoiceNumber();
            }

            // Update status
            $invoice->update([
                'status' => 'finalized',
                'finalized_at' => now(),
                'finalized_by' => Auth::id(),
            ]);

            // Update all time entries linked to this invoice (non-deferred)
            $allTimeEntries = TimeEntry::where('invoice_id', $invoice->id)->get();
            foreach ($allTimeEntries as $entry) {
                $entry->update([
                    'is_finalized' => true,
                    'finalized_at' => now(),
                    'final_invoice_number' => $invoice->invoice_number,
                ]);
            }

            // Handle deferred items for next month
            $deferredLines = $invoice->lines()->where('defer_to_next_month', true)->get();

            foreach ($deferredLines as $deferredLine) {
                // For time entry lines, unlink the time entries so they can be included in next month
                if ($deferredLine->source_type === 'time_entry') {
                    // Find all time entries linked to this deferred line
                    $timeEntries = TimeEntry::where('invoice_line_id', $deferredLine->id)->get();

                    // Reset their invoice linkage so they appear as available for next month
                    foreach ($timeEntries as $entry) {
                        $entry->update([
                            'invoice_id' => null,
                            'invoice_line_id' => null,
                            'is_invoiced' => false,
                            'invoiced_at' => null,
                            'is_finalized' => false,
                            'finalized_at' => null,
                            'final_invoice_number' => null,
                            // Keep invoiced_* data as history
                            // Mark as deferred for tracking
                            'was_deferred' => true,
                            'deferred_at' => now(),
                            'deferred_by' => Auth::id(),
                            'defer_reason' => 'Deferred from invoice ' . $invoice->invoice_number,
                        ]);
                    }

                    Log::info('Time entries unlinked from deferred line', [
                        'line_id' => $deferredLine->id,
                        'time_entry_count' => $timeEntries->count(),
                        'invoice_id' => $invoice->id
                    ]);
                }
            }

            DB::commit();

            Log::info('Invoice finalized', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'user_id' => Auth::id()
            ]);

            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Invoice finalized successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to finalize invoice', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoice->id
            ]);

            return back()->withErrors(['error' => 'Failed to finalize invoice: ' . $e->getMessage()]);
        }
    }

    /**
     * Finalize invoice within existing transaction (called from update method)
     */
    protected function finalizeInvoiceInTransaction(Invoice $invoice): void
    {
        // Generate final invoice number if not set
        if (!$invoice->invoice_number || str_starts_with($invoice->invoice_number, 'DRAFT-')) {
            $invoice->invoice_number = $this->generateFinalInvoiceNumber();
        }

        // Update status
        $invoice->update([
            'status' => 'finalized',
            'finalized_at' => now(),
            'finalized_by' => Auth::id(),
        ]);

        // Update all time entries linked to this invoice (non-deferred)
        // Time entries are linked via invoice_line_id, not directly to invoice_id
        $invoiceLineIds = $invoice->lines()->pluck('id');
        $allTimeEntries = TimeEntry::whereIn('invoice_line_id', $invoiceLineIds)->get();
        foreach ($allTimeEntries as $entry) {
            $entry->update([
                'is_finalized' => true,
                'finalized_at' => now(),
                'final_invoice_number' => $invoice->invoice_number,
            ]);
        }

        // Handle deferred items for next month
        $deferredLines = $invoice->lines()->where('defer_to_next_month', true)->get();

        foreach ($deferredLines as $deferredLine) {
            // For time entry lines, unlink the time entries so they can be included in next month
            if ($deferredLine->source_type === 'time_entry') {
                // Find all time entries linked to this deferred line
                $timeEntries = TimeEntry::where('invoice_line_id', $deferredLine->id)->get();

                // Reset their invoice linkage so they appear as available for next month
                foreach ($timeEntries as $entry) {
                    $entry->update([
                        'invoice_id' => null,
                        'invoice_line_id' => null,
                        'is_invoiced' => false,
                        'invoiced_at' => null,
                        'is_finalized' => false,
                        'finalized_at' => null,
                        'final_invoice_number' => null,
                        // Keep invoiced_* data as history
                        // Mark as deferred for tracking
                        'was_deferred' => true,
                        'deferred_at' => now(),
                        'deferred_by' => Auth::id(),
                        'defer_reason' => 'Deferred from invoice ' . $invoice->invoice_number,
                    ]);
                }

                Log::info('Time entries unlinked from deferred line during finalization', [
                    'line_id' => $deferredLine->id,
                    'time_entry_count' => $timeEntries->count(),
                    'invoice_id' => $invoice->id
                ]);
            }
        }

        Log::info('Invoice finalized via Save & Finalize', [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'user_id' => Auth::id()
        ]);
    }

    /**
     * Execute deferred items immediately (without finalizing invoice)
     */
    public function executeDefers(Request $request, Invoice $invoice)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied.');
        }

        // Check if invoice is still draft
        if ($invoice->status !== 'draft') {
            return back()->with('error', 'Only draft invoices can have defers executed.');
        }

        try {
            DB::beginTransaction();

            // Find deferred lines
            $deferredLines = $invoice->lines()->where('defer_to_next_month', true)->get();

            if ($deferredLines->count() === 0) {
                return back()->with('info', 'No deferred items found in this invoice.');
            }

            $deferredCount = 0;
            $timeEntriesUnlinked = 0;

            foreach ($deferredLines as $deferredLine) {
                // For time entry lines, unlink the time entries so they can be included in next month
                if ($deferredLine->source_type === 'time_entry') {
                    // Find all time entries linked to this deferred line
                    $timeEntries = TimeEntry::where('invoice_line_id', $deferredLine->id)->get();

                    // Reset their invoice linkage so they appear as available for next month
                    foreach ($timeEntries as $entry) {
                        $entry->update([
                            'invoice_id' => null,
                            'invoice_line_id' => null,
                            'is_invoiced' => false,
                            'invoiced_at' => null,
                            'is_finalized' => false,
                            'finalized_at' => null,
                            'final_invoice_number' => null,
                            // Keep invoiced_* data as history
                            // Mark as deferred for tracking
                            'was_deferred' => true,
                            'deferred_at' => now(),
                            'deferred_by' => Auth::id(),
                            'defer_reason' => 'Deferred from invoice ' . ($invoice->invoice_number ?: 'DRAFT-' . $invoice->id),
                        ]);
                    }

                    $timeEntriesUnlinked += $timeEntries->count();

                    // Remove the deferred line from the invoice
                    $deferredLine->delete();
                    $deferredCount++;

                    Log::info('Deferred line executed', [
                        'line_id' => $deferredLine->id,
                        'time_entry_count' => $timeEntries->count(),
                        'invoice_id' => $invoice->id,
                        'executed_by' => Auth::id()
                    ]);
                } else {
                    // For non-time-entry lines, just remove them (they can be manually re-added next month)
                    $deferredLine->delete();
                    $deferredCount++;
                }
            }

            // Recalculate invoice totals after removing deferred lines
            $this->recalculateInvoiceTotals($invoice);

            DB::commit();

            $message = "Executed {$deferredCount} deferred item(s).";
            if ($timeEntriesUnlinked > 0) {
                $message .= " {$timeEntriesUnlinked} time entries are now available for next month's invoice.";
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to execute defers', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoice->id,
                'user_id' => Auth::id()
            ]);

            return back()->with('error', 'Failed to execute defers: ' . $e->getMessage());
        }
    }

    /**
     * Delete invoice line
     */
    public function deleteLine(Invoice $invoice, InvoiceLine $line)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied.');
        }

        // Check if invoice is editable
        if ($invoice->status !== 'draft') {
            return response()->json(['error' => 'Only draft invoices can be edited.'], 400);
        }

        // Check line belongs to invoice
        if ($line->invoice_id !== $invoice->id) {
            return response()->json(['error' => 'Invalid line item.'], 400);
        }

        try {
            // If this was a time entry line, unmark the time entries
            if ($line->source_type === 'time_entry' && $line->metadata) {
                $metadata = json_decode($line->metadata, true);
                if (isset($metadata['time_entry_ids'])) {
                    TimeEntry::whereIn('id', $metadata['time_entry_ids'])
                        ->update(['invoice_id' => null]);
                }
            }

            $line->delete();

            // Recalculate totals
            $this->recalculateInvoiceTotals($invoice);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete line item.'], 500);
        }
    }

    /**
     * Add manual line to invoice
     */
    public function addLine(Request $request, Invoice $invoice)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied.');
        }

        // Check if invoice is editable
        if ($invoice->status !== 'draft') {
            return response()->json(['error' => 'Only draft invoices can be edited.'], 400);
        }

        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
            'unit_price' => 'required|numeric|min:0',
            'category' => 'required|in:work,service,cost,adjustment,discount',
        ]);

        try {
            $maxSortOrder = $invoice->lines()->max('sort_order') ?? 0;

            $line = InvoiceLine::create([
                'invoice_id' => $invoice->id,
                'source_type' => 'manual',
                'description' => $validated['description'],
                'quantity' => $validated['quantity'],
                'unit' => $validated['unit'],
                'unit_price' => $validated['unit_price'],
                'amount' => $validated['quantity'] * $validated['unit_price'],
                'category' => $validated['category'],
                'is_billable' => true,
                'sort_order' => $maxSortOrder + 10,
            ]);

            // Recalculate totals
            $this->recalculateInvoiceTotals($invoice);

            return response()->json([
                'success' => true,
                'line' => $line
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to add line item.'], 500);
        }
    }

    /**
     * Recalculate invoice totals
     */
    protected function recalculateInvoiceTotals(Invoice $invoice): void
    {
        $lines = $invoice->lines()->where('defer_to_next_month', false)->get();
        
        // Use line_total_ex_vat instead of amount for accurate calculations
        $workAmount = $lines->where('category', 'work')->sum('line_total_ex_vat');
        $serviceAmount = $lines->where('category', 'service')->sum('line_total_ex_vat');
        $additionalCosts = $lines->where('category', 'cost')->sum('line_total_ex_vat');
        
        $subtotal = $lines->where('is_billable', true)->sum('line_total_ex_vat');
        $vatAmount = $subtotal * ($invoice->vat_rate / 100);
        $totalAmount = $subtotal + $vatAmount;
        
        // Calculate rollover
        $totalCosts = $workAmount + $serviceAmount;
        $nextMonthRollover = 0;
        
        if ($invoice->project->fee_rollover_enabled && $invoice->total_budget > 0) {
            if ($totalCosts < $invoice->total_budget) {
                // Positive rollover (budget remaining)
                $nextMonthRollover = $invoice->total_budget - $totalCosts;
            } else {
                // Negative rollover (budget exceeded)
                $nextMonthRollover = -($totalCosts - $invoice->total_budget);
            }
        }
        
        $invoice->update([
            'work_amount' => $workAmount,
            'service_amount' => $serviceAmount,
            'additional_costs' => $additionalCosts,
            'subtotal_ex_vat' => $subtotal,
            'vat_amount' => $vatAmount,
            'total_inc_vat' => $totalAmount,
            'next_month_rollover' => $nextMonthRollover,
        ]);
    }

    /**
     * Generate final invoice number
     */
    protected function generateFinalInvoiceNumber(): string
    {
        $year = now()->year;
        $lastInvoice = Invoice::where('invoice_number', 'like', "INV-{$year}-%")
            ->where('invoice_number', 'not like', 'DRAFT-%')
            ->orderBy('invoice_number', 'desc')
            ->first();
        
        if ($lastInvoice) {
            $lastNumber = intval(substr($lastInvoice->invoice_number, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return sprintf("INV-%s-%04d", $year, $newNumber);
    }

    /**
     * Download invoice as PDF
     */
    public function download(Invoice $invoice)
    {
        // TODO: Implement PDF generation
        return back()->with('info', 'PDF generation will be implemented soon.');
    }

    /**
     * Delete draft invoice
     */
    public function destroy(Invoice $invoice)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can delete invoices.');
        }
        
        // Only allow deletion of draft invoices
        if ($invoice->status !== 'draft') {
            return back()->with('error', 'Only draft invoices can be deleted. Finalized invoices cannot be removed.');
        }
        
        // Check company access
        if (Auth::user()->role !== 'super_admin' && $invoice->invoicing_company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied.');
        }
        
        try {
            DB::beginTransaction();
            
            // Reset time entries and restore previous defer state if applicable
            $timeEntries = TimeEntry::where('invoice_id', $invoice->id)->get();

            foreach ($timeEntries as $entry) {
                $updateData = [
                    'invoice_id' => null,
                    'invoice_line_id' => null,
                    'is_invoiced' => false,
                    'invoiced_at' => null,
                    'invoiced_hours' => null,
                    'invoiced_rate' => null,
                    'invoiced_description' => null,
                    'invoiced_modified_at' => null,
                    'invoiced_modified_by' => null,
                ];

                // Restore previous defer state if entry was previously deferred
                if ($entry->was_previously_deferred) {
                    $updateData['was_deferred'] = true;
                    $updateData['deferred_at'] = $entry->previous_deferred_at;
                    $updateData['deferred_by'] = $entry->previous_deferred_by;
                    $updateData['defer_reason'] = $entry->previous_defer_reason;
                    // Clear the previous state tracking
                    $updateData['was_previously_deferred'] = false;
                    $updateData['previous_deferred_at'] = null;
                    $updateData['previous_deferred_by'] = null;
                    $updateData['previous_defer_reason'] = null;
                }

                $entry->update($updateData);
            }
            
            // Delete invoice lines
            $invoice->lines()->delete();
            
            // Delete the invoice
            $invoice->delete();
            
            DB::commit();
            
            return redirect()->route('invoices.index')
                ->with('success', 'Draft invoice has been deleted successfully.');
                
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error deleting invoice', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to delete invoice. Please try again.');
        }
    }

    /**
     * Send invoice by email
     */
    public function send(Invoice $invoice)
    {
        // TODO: Implement email sending
        return back()->with('info', 'Email sending will be implemented soon.');
    }

    /**
     * Update invoice status
     */
    public function updateStatus(Request $request, Invoice $invoice)
    {
        // Authorization - alleen voor admin/super_admin
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can update invoice status.');
        }

        $validated = $request->validate([
            'status' => 'required|in:draft,finalized,sent,paid,overdue,cancelled'
        ]);

        // Status workflow validatie
        $allowedTransitions = [
            'draft' => ['finalized', 'cancelled'],
            'finalized' => ['sent', 'cancelled'],
            'sent' => ['paid', 'overdue', 'cancelled'],
            'overdue' => ['paid', 'cancelled'],
            'paid' => [], // Geen transitie vanaf paid
            'cancelled' => [] // Geen transitie vanaf cancelled
        ];

        if (!in_array($validated['status'], $allowedTransitions[$invoice->status] ?? [])) {
            return back()->with('error', 'Invalid status transition.');
        }

        // Update status
        $invoice->status = $validated['status'];
        
        // Set specifieke velden voor bepaalde statussen
        if ($validated['status'] === 'sent') {
            $invoice->sent_at = now();
            $invoice->due_date = now()->addDays(30);
        } elseif ($validated['status'] === 'paid') {
            $invoice->paid_at = now();
            $invoice->paid_amount = $invoice->total_amount;
        } elseif ($validated['status'] === 'finalized') {
            $invoice->finalized_at = now();
            $invoice->finalized_by = Auth::id();
            if (!$invoice->invoice_number) {
                $invoice->invoice_number = $this->generateFinalInvoiceNumber();
            }
        }
        
        $invoice->save();

        $statusMessages = [
            'finalized' => 'Invoice has been finalized.',
            'sent' => 'Invoice marked as sent.',
            'paid' => 'Invoice marked as paid.',
            'cancelled' => 'Invoice has been cancelled.'
        ];

        return back()->with('success', $statusMessages[$validated['status']] ?? 'Invoice status updated.');
    }

    /**
     * Update linked time entries with invoiced data
     */
    protected function updateLinkedTimeEntries(InvoiceLine $line, array $invoicedData)
    {
        // Find all time entries linked to this invoice line
        $timeEntries = TimeEntry::where('invoice_line_id', $line->id)->get();

        if ($timeEntries->count() > 0) {
            // For multiple time entries linked to one line, distribute the invoiced hours proportionally
            $totalOriginalHours = $timeEntries->sum('hours');

            if ($totalOriginalHours > 0) {
                foreach ($timeEntries as $entry) {
                    $proportion = $entry->hours / $totalOriginalHours;
                    $proportionalHours = $invoicedData['invoiced_hours'] * $proportion;

                    $entry->update([
                        'invoiced_hours' => $proportionalHours,
                        'invoiced_rate' => $invoicedData['invoiced_rate'],
                        'invoiced_description' => $invoicedData['invoiced_description'],
                        'invoiced_modified_at' => $invoicedData['invoiced_modified_at'],
                        'invoiced_modified_by' => $invoicedData['invoiced_modified_by'],
                    ]);
                }
            }
        }
    }
}