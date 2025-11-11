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
        
        // Get filter options - met customer relatie voor dropdown display
        // Toon alleen projecten met ongefactureerde items (approved time entries of billable costs)
        $projects = Project::with('customer')
                    ->where('status', '!=', 'cancelled')
                    ->where(function($q) {
                        // Projecten met ongefactureerde goedgekeurde tijd registraties
                        $q->whereHas('timeEntries', function($q2) {
                            $q2->where('status', 'approved')
                               ->where('is_billable', 'billable')
                               ->where(function($q3) {
                                   $q3->whereNull('is_invoiced')
                                      ->orWhere('is_invoiced', false);
                               });
                        })
                        // OF projecten met recurring/additional costs die nog gefactureerd moeten worden
                        ->orWhereHas('additionalCosts', function($q2) {
                            $q2->where('is_active', true)
                               ->where('auto_invoice', true);
                        })
                        // OF recurring projecten (die altijd factureerbaar zijn per periode)
                        ->orWhere('is_recurring', true);
                    })
                    ->when($user->role !== 'super_admin', function($q) use ($user) {
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
        
        // Get projects - alleen met ongefactureerde items
        $projects = Project::when($user->role !== 'super_admin', function($q) use ($user) {
                        $q->where('company_id', $user->company_id);
                    })
                    ->with(['customer', 'milestones.tasks'])
                    ->where('status', '!=', 'cancelled')
                    ->where(function($q) {
                        // Projecten met ongefactureerde goedgekeurde tijd registraties
                        $q->whereHas('timeEntries', function($q2) {
                            $q2->where('status', 'approved')
                               ->where('is_billable', 'billable')
                               ->where(function($q3) {
                                   $q3->whereNull('is_invoiced')
                                      ->orWhere('is_invoiced', false);
                               });
                        })
                        // OF projecten met recurring/additional costs die nog gefactureerd moeten worden
                        ->orWhereHas('additionalCosts', function($q2) {
                            $q2->where('is_active', true)
                               ->where('auto_invoice', true);
                        })
                        // OF recurring projecten (die altijd factureerbaar zijn per periode)
                        ->orWhere('is_recurring', true);
                    })
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
                // Show customers that are linked via legacy company_id OR via customer_companies pivot
                $q->where(function($query) {
                    $query->where('company_id', Auth::user()->company_id)
                          ->orWhereHas('companies', function($subQuery) {
                              $subQuery->where('companies.id', Auth::user()->company_id);
                          });
                });
            })
            ->orderBy('name')
            ->get();

        // Always ensure the invoice's customer is in the list (even if filtered out)
        if ($invoice->customer_id && !$customers->contains('id', $invoice->customer_id)) {
            $customers->prepend($invoice->customer);
        }

        // Get projects for project selection (met customer relatie voor display)
        // Include all non-cancelled projects + het current project van de invoice (als die er is)
        $projects = Project::with('customer')
            ->where('status', '!=', 'cancelled')
            ->when(Auth::user()->role !== 'super_admin', function($q) {
                $q->where('company_id', Auth::user()->company_id);
            })
            ->orderBy('name')
            ->get();

        // Zorgen dat het huidige project van de invoice altijd in de lijst staat (ook al is het cancelled)
        if ($invoice->project_id && !$projects->contains('id', $invoice->project_id)) {
            $currentProject = Project::with('customer')->find($invoice->project_id);
            if ($currentProject) {
                $projects->prepend($currentProject);
            }
        }

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

            // Update all ProjectAdditionalCosts linked to this invoice
            // Find all cost invoice lines that are linked to ProjectAdditionalCosts
            $costLines = $invoice->lines()
                ->where('category', 'cost')
                ->where('source_type', 'additional_cost')
                ->whereNotNull('source_id')
                ->get();

            foreach ($costLines as $costLine) {
                $projectCost = ProjectAdditionalCost::find($costLine->source_id);

                if ($projectCost) {
                    // Markeer als niet-actief zodat deze cost niet meer automatisch gefactureerd wordt
                    $projectCost->update([
                        'is_active' => false,
                        'notes' => ($projectCost->notes ? $projectCost->notes . "\n\n" : '') .
                                  'Invoiced: ' . $invoice->invoice_number . ' (' . now()->format('d-m-Y') . ')',
                    ]);

                    Log::info('ProjectAdditionalCost marked as invoiced', [
                        'project_cost_id' => $projectCost->id,
                        'name' => $projectCost->name,
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                    ]);
                }
            }

            // Handle deferred items for next month
            $deferredLines = $invoice->lines()->where('defer_to_next_month', true)->get();

            foreach ($deferredLines as $deferredLine) {
                // Build defer history tracking
                $metadata = $deferredLine->metadata ? json_decode($deferredLine->metadata, true) : [];
                $deferHistory = $metadata['defer_history'] ?? [];

                // Add current defer to history
                $deferHistory[] = [
                    'from_period_start' => $invoice->period_start->format('Y-m-d'),
                    'from_period_end' => $invoice->period_end->format('Y-m-d'),
                    'from_invoice_id' => $invoice->id,
                    'from_invoice_number' => $invoice->invoice_number,
                    'deferred_at' => now()->format('Y-m-d H:i:s'),
                    'deferred_by' => Auth::id(),
                    'defer_count' => count($deferHistory) + 1,
                ];

                // Update metadata with defer history
                $metadata['defer_history'] = $deferHistory;

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
                            'defer_reason' => 'Deferred from invoice ' . $invoice->invoice_number . ' (' . $invoice->period_start->format('M Y') . ')',
                            // Defer history is stored in invoice_line metadata, not here
                        ]);
                    }

                    Log::info('Time entries unlinked from deferred line with history', [
                        'line_id' => $deferredLine->id,
                        'time_entry_count' => $timeEntries->count(),
                        'invoice_id' => $invoice->id,
                        'defer_count' => count($deferHistory),
                        'original_period' => $deferHistory[0]['from_period_start'] ?? 'unknown',
                    ]);
                }

                // Store deferred line metadata for reconstruction in next invoice
                $deferredLine->update([
                    'metadata' => json_encode($metadata),
                ]);
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

        // Update all ProjectAdditionalCosts linked to this invoice
        $costLines = $invoice->lines()
            ->where('category', 'cost')
            ->where('source_type', 'additional_cost')
            ->whereNotNull('source_id')
            ->get();

        foreach ($costLines as $costLine) {
            $projectCost = ProjectAdditionalCost::find($costLine->source_id);

            if ($projectCost) {
                $projectCost->update([
                    'is_active' => false,
                    'notes' => ($projectCost->notes ? $projectCost->notes . "\n\n" : '') .
                              'Invoiced: ' . $invoice->invoice_number . ' (' . now()->format('d-m-Y') . ')',
                ]);

                Log::info('ProjectAdditionalCost marked as invoiced (in transaction)', [
                    'project_cost_id' => $projectCost->id,
                    'name' => $projectCost->name,
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                ]);
            }
        }

        // Handle deferred items for next month
        $deferredLines = $invoice->lines()->where('defer_to_next_month', true)->get();

        foreach ($deferredLines as $deferredLine) {
            // Build defer history tracking
            $metadata = $deferredLine->metadata ? json_decode($deferredLine->metadata, true) : [];
            $deferHistory = $metadata['defer_history'] ?? [];

            // Add current defer to history
            $deferHistory[] = [
                'from_period_start' => $invoice->period_start->format('Y-m-d'),
                'from_period_end' => $invoice->period_end->format('Y-m-d'),
                'from_invoice_id' => $invoice->id,
                'from_invoice_number' => $invoice->invoice_number,
                'deferred_at' => now()->format('Y-m-d H:i:s'),
                'deferred_by' => Auth::id(),
                'defer_count' => count($deferHistory) + 1,
            ];

            // Update metadata with defer history
            $metadata['defer_history'] = $deferHistory;

            // Sla originele milestone/task IDs op voor restore bij unfinalize
            if (!isset($metadata['original_milestone_id'])) {
                $metadata['original_milestone_id'] = $deferredLine->group_milestone_id;
            }
            if (!isset($metadata['original_task_id'])) {
                $metadata['original_task_id'] = $deferredLine->group_task_id;
            }

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
                        'defer_reason' => 'Deferred from invoice ' . $invoice->invoice_number . ' (' . $invoice->period_start->format('M Y') . ')',
                    ]);
                }

                Log::info('Time entries unlinked from deferred line during finalization', [
                    'line_id' => $deferredLine->id,
                    'time_entry_count' => $timeEntries->count(),
                    'invoice_id' => $invoice->id,
                    'defer_count' => count($deferHistory),
                    'original_period' => $deferHistory[0]['from_period_start'] ?? 'unknown',
                ]);
            }

            // Store deferred line metadata for reconstruction in next invoice
            $deferredLine->update([
                'metadata' => json_encode($metadata),
            ]);
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

        // KRITIEKE BUDGET LOGICA:
        // - In Fee costs (is_billable = false) tellen mee bij work_amount (budget usage)
        // - Additional costs (is_billable = true) tellen NIET mee bij budget, maar WEL bij factuur totaal

        // Work amount = Time entries + In Fee costs (voor budget tracking)
        $workAmount = $lines->where('category', 'work')->sum('line_total_ex_vat');
        $inFeeCosts = $lines->where('category', 'cost')->where('is_billable', false)->sum('line_total_ex_vat');
        $workAmount += $inFeeCosts; // In Fee costs tellen mee bij budget usage

        // Service packages
        $serviceAmount = $lines->where('category', 'service')->sum('line_total_ex_vat');

        // Additional costs = ALLEEN billable costs (komen bovenop factuur)
        $additionalCosts = $lines->where('category', 'cost')->where('is_billable', true)->sum('line_total_ex_vat');

        // Subtotal voor factuur = ALLEEN billable items (work + service + additional)
        // In Fee costs zijn al in maandelijkse fee, dus NIET in factuur subtotal
        $subtotal = $lines->where('is_billable', true)->sum('line_total_ex_vat');
        $vatAmount = $subtotal * ($invoice->vat_rate / 100);
        $totalAmount = $subtotal + $vatAmount;

        // Calculate rollover - gebaseerd op work + service (binnen budget)
        $totalCosts = $workAmount + $serviceAmount; // Inclusief in_fee costs
        $nextMonthRollover = 0;

        // KRITIEK: Gebruik monthly_budget (NIET total_budget) voor nieuwe rollover berekening
        // Anders wordt de oude rollover dubbel geteld!
        if ($invoice->project && $invoice->project->fee_rollover_enabled && $invoice->monthly_budget > 0) {
            if ($totalCosts < $invoice->monthly_budget) {
                // Positive rollover (budget remaining THIS month)
                $nextMonthRollover = $invoice->monthly_budget - $totalCosts;
            } else {
                // Negative rollover (budget exceeded THIS month)
                $nextMonthRollover = -($totalCosts - $invoice->monthly_budget);
            }
        }

        $invoice->update([
            'work_amount' => $workAmount, // Inclusief in_fee costs
            'service_amount' => $serviceAmount,
            'additional_costs' => $additionalCosts, // ALLEEN billable additional costs
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
     * Revert finalized invoice back to draft (super_admin/admin only)
     */
    public function unfinalize(Invoice $invoice)
    {
        // Authorization - ALLEEN super_admin en admin
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only super administrators and administrators can revert invoices.');
        }

        // Check company access
        if (Auth::user()->role !== 'super_admin' && $invoice->invoicing_company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied.');
        }

        // Only allow unfinalizing of 'finalized' status (NOT sent/paid)
        if ($invoice->status !== 'finalized') {
            return back()->with('error', 'Only finalized invoices can be reverted to draft. Sent or paid invoices cannot be modified.');
        }

        try {
            DB::beginTransaction();

            // Keep the invoice number for audit trail, just change status back
            $originalInvoiceNumber = $invoice->invoice_number;

            $invoice->update([
                'status' => 'draft',
                'finalized_at' => null,
                'finalized_by' => null,
            ]);

            // Reset all time entries linked to this invoice
            $invoiceLineIds = $invoice->lines()->pluck('id');
            $timeEntries = TimeEntry::whereIn('invoice_line_id', $invoiceLineIds)->get();

            foreach ($timeEntries as $entry) {
                $entry->update([
                    'is_finalized' => false,
                    'finalized_at' => null,
                    'final_invoice_number' => null,
                    // Keep is_invoiced and invoice_id so they stay linked to draft
                ]);
            }

            // If any items were deferred, restore them to the invoice
            // (They were removed during finalization, now we restore them)
            $deferredLines = $invoice->lines()->where('defer_to_next_month', true)->get();
            $affectedInvoiceIds = []; // Track welke toekomstige facturen we moeten herberekenen

            foreach ($deferredLines as $deferredLine) {
                if ($deferredLine->source_type === 'time_entry') {
                    // Find time entries that were marked as deferred
                    $metadata = $deferredLine->metadata ? json_decode($deferredLine->metadata, true) : [];
                    $timeEntryIds = $metadata['time_entry_ids'] ?? [];

                    if (!empty($timeEntryIds)) {
                        // Zoek de time entries - ze kunnen 2 statussen hebben:
                        // 1. was_deferred = true (nog niet in nieuwe factuur opgenomen)
                        // 2. is_invoiced = true met ander invoice_id (al in nieuwe factuur)
                        $deferredEntries = TimeEntry::whereIn('id', $timeEntryIds)->get();

                        foreach ($deferredEntries as $entry) {
                            // Als item al in een ANDERE factuur zit, haal het daar eerst uit
                            if ($entry->invoice_id && $entry->invoice_id !== $invoice->id) {
                                // Track deze factuur voor herberekening
                                $affectedInvoiceIds[] = $entry->invoice_id;

                                // Delete de invoice line in de andere factuur
                                if ($entry->invoice_line_id) {
                                    \App\Models\InvoiceLine::where('id', $entry->invoice_line_id)->delete();
                                    Log::info('Removed deferred item from future invoice during unfinalize', [
                                        'time_entry_id' => $entry->id,
                                        'removed_from_invoice_id' => $entry->invoice_id,
                                        'restored_to_invoice_id' => $invoice->id,
                                    ]);
                                }
                            }

                            // Herstel originele milestone/task IDs uit metadata
                            $originalMilestoneId = $metadata['original_milestone_id'] ?? $entry->project_milestone_id;
                            $originalTaskId = $metadata['original_task_id'] ?? $entry->project_task_id;

                            // Reset time entry terug naar deze (Augustus) invoice
                            $entry->update([
                                'project_id' => $invoice->project_id, // Terug naar origineel project
                                'project_milestone_id' => $originalMilestoneId,
                                'project_task_id' => $originalTaskId,
                                'invoice_id' => $invoice->id,
                                'invoice_line_id' => $deferredLine->id,
                                'is_invoiced' => true,
                                'invoiced_at' => now(),
                                'was_deferred' => false,
                                'deferred_at' => null,
                                'deferred_by' => null,
                                'defer_reason' => null,
                            ]);
                        }
                    }
                }
            }

            // Recalculate totals van deze invoice
            $this->recalculateInvoiceTotals($invoice);

            // Herbereken ook de toekomstige facturen waar we items uit gehaald hebben
            foreach (array_unique($affectedInvoiceIds) as $affectedInvoiceId) {
                $affectedInvoice = Invoice::find($affectedInvoiceId);
                if ($affectedInvoice) {
                    $this->recalculateInvoiceTotals($affectedInvoice);
                    Log::info('Recalculated totals for affected future invoice after unfinalize', [
                        'future_invoice_id' => $affectedInvoiceId,
                        'restored_to_invoice_id' => $invoice->id,
                    ]);
                }
            }

            DB::commit();

            Log::info('Invoice unfinalized (reverted to draft)', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $originalInvoiceNumber,
                'user_id' => Auth::id(),
                'user_role' => Auth::user()->role,
            ]);

            return redirect()->route('invoices.edit', $invoice)
                ->with('success', 'Invoice has been reverted to draft. You can now make changes and defer items if needed.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to unfinalize invoice', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoice->id,
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Failed to revert invoice: ' . $e->getMessage());
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

    /**
     * Show spreadsheet view for invoice editing
     */
    public function spreadsheet(Invoice $invoice)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can edit invoices.');
        }

        // Company isolation
        if (Auth::user()->role !== 'super_admin' && $invoice->invoicing_company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only edit invoices from your own company.');
        }

        // Alleen draft invoices kunnen bewerkt worden
        if ($invoice->status !== 'draft') {
            return redirect()->route('invoices.edit', $invoice)
                ->with('error', 'Only draft invoices can be edited in spreadsheet view.');
        }

        // Load relationships
        $invoice->load([
            'lines' => function ($query) {
                $query->with(['milestone', 'task'])
                      ->orderBy('sort_order');
            },
            'customer',
            'invoicingCompany',
            'project'
        ]);

        // Split lines into costs en time entries
        $allLines = $invoice->lines->filter(function($line) {
            return $line->quantity > 0; // Alleen regels met quantity > 0
        });

        // Additional Costs - alleen category='cost'
        $costsData = $allLines->filter(function($line) {
            return $line->category === 'cost';
        })->map(function($line) {
            // Fee type bepalen: is_billable = additional, anders in_fee
            $feeType = $line->is_billable ? 'additional' : 'in_fee';

            return [
                'id' => $line->id,
                'description' => trim($line->description),
                'quantity' => (float) $line->quantity,
                'price' => (float) $line->unit_price,
                'total' => (float) $line->line_total_ex_vat,
                'fee_type' => $feeType,
                'is_billable' => (bool) $line->is_billable,
                'defer_to_next_month' => (bool) $line->defer_to_next_month
            ];
        })->values();

        // Time Entry Lines - category != 'cost'
        $linesData = $allLines->filter(function($line) {
            // Filter 1: Alleen non-cost items
            if ($line->category === 'cost') {
                return false;
            }

            // Filter 2: Verwijder header regels (regels ZONDER time entries)
            // Headers hebben GEEN metadata met time_entry_ids
            // Echte werk regels hebben WEL metadata met time_entry_ids

            // Parse metadata
            $metadata = $line->metadata;
            if (is_string($metadata)) {
                $metadata = json_decode($metadata, true);
            }

            // Als er GEEN time_entry_ids zijn, is het een header → filter weg
            if (!$metadata || !isset($metadata['time_entry_ids']) || empty($metadata['time_entry_ids'])) {
                return false; // Dit is een header, filter weg
            }

            // Alleen tonen als het echte work is (met time_entry_ids)
            return true;
        })->map(function($line) {
            $milestoneName = $line->milestone ? $line->milestone->name : '';
            $taskName = $line->task ? $line->task->name : '';

            // Haal de echte time entry descriptions op uit de metadata
            $realDescription = '';

            // Parse metadata - kan string of array zijn
            $metadata = $line->metadata;
            if (is_string($metadata)) {
                $metadata = json_decode($metadata, true);
            }

            if ($metadata && isset($metadata['time_entry_ids'])) {
                $timeEntryIds = $metadata['time_entry_ids'];
                $timeEntries = \App\Models\TimeEntry::whereIn('id', $timeEntryIds)->get();

                // Verzamel alle unieke descriptions
                $descriptions = $timeEntries->pluck('description')
                    ->map(fn($d) => trim($d))
                    ->filter()
                    ->unique()
                    ->values();

                // Voeg samen met komma separator
                $realDescription = $descriptions->join(', ');
            }

            // Fallback naar invoice line description als geen time entries
            if (empty($realDescription)) {
                $realDescription = trim($line->description);
            }

            return [
                'id' => $line->id,
                'type' => $line->line_type ?? 'normal',
                'milestone' => $milestoneName,
                'task' => $taskName,
                'description' => $realDescription,
                'quantity' => (float) $line->quantity,
                'price' => (float) $line->unit_price,
                'total' => (float) $line->line_total_ex_vat,
                'defer_to_next_month' => (bool) $line->defer_to_next_month
            ];
        })->values();

        // DEBUG: Log de complete linesData structure die naar de view wordt gestuurd
        Log::debug('FINAL linesData being sent to view', [
            'invoice_id' => $invoice->id,
            'lines_count' => $linesData->count(),
            'first_5_lines' => $linesData->take(5)->toArray(), // Eerste 5 regels om te controleren
        ]);

        return view('invoices.spreadsheet', compact('invoice', 'linesData', 'costsData'));
    }

    /**
     * Update invoice from spreadsheet data
     */
    public function updateSpreadsheet(Request $request, Invoice $invoice)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can edit invoices.');
        }

        // Company isolation
        if (Auth::user()->role !== 'super_admin' && $invoice->invoicing_company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only edit invoices from your own company.');
        }

        // Alleen draft invoices kunnen bewerkt worden
        if ($invoice->status !== 'draft') {
            return redirect()->route('invoices.edit', $invoice)
                ->with('error', 'Only draft invoices can be edited.');
        }

        try {
            DB::beginTransaction();

            // Validatie van spreadsheet data
            $validated = $request->validate([
                'lines' => 'required|array',
                'lines.*.id' => 'required|exists:invoice_lines,id',
                'lines.*.milestone' => 'sometimes|string|max:255', // NIEUW: Voor reconstructie
                'lines.*.task' => 'sometimes|string|max:255', // NIEUW: Voor reconstructie
                'lines.*.description' => 'sometimes|string|max:1000', // Sometimes want milestone/task headers kunnen lege description hebben
                'lines.*.quantity' => 'required|numeric|min:0',
                'lines.*.unit_price' => 'required|numeric|min:0',
                'lines.*.defer_to_next_month' => 'sometimes|boolean',
                'costs' => 'sometimes|array',
                'costs.*.id' => 'required_with:costs|exists:invoice_lines,id',
                'costs.*.description' => 'required_with:costs|string|max:1000',
                'costs.*.quantity' => 'required_with:costs|numeric|min:0',
                'costs.*.unit_price' => 'required_with:costs|numeric|min:0',
                'costs.*.defer_to_next_month' => 'sometimes|boolean',
                'costs.*.fee_type' => 'sometimes|in:in_fee,additional',
                'new_costs' => 'sometimes|array',
                'new_costs.*.description' => 'required_with:new_costs|string|max:1000',
                'new_costs.*.quantity' => 'required_with:new_costs|numeric|min:0',
                'new_costs.*.unit_price' => 'required_with:new_costs|numeric|min:0',
                'new_costs.*.defer_to_next_month' => 'sometimes|boolean',
                'new_costs.*.fee_type' => 'sometimes|in:in_fee,additional',
                'deleted_line_ids' => 'sometimes|array',
                'deleted_line_ids.*' => 'integer|exists:invoice_lines,id',
                'deleted_cost_ids' => 'sometimes|array',
                'deleted_cost_ids.*' => 'integer|exists:invoice_lines,id',
            ]);

            // Update alle time entry invoice lines
            foreach ($validated['lines'] as $lineData) {
                $line = InvoiceLine::findOrFail($lineData['id']);

                // Verify line belongs to this invoice
                if ($line->invoice_id !== $invoice->id) {
                    throw new \Exception('Invalid line ID');
                }

                // BELANGRIJK: Reconstrueer description met milestone/task context
                // De frontend stuurt milestone en task gescheiden mee, maar de database verwacht
                // de volledige description inclusief milestone/task namen
                $description = $lineData['description'];
                $milestone = $lineData['milestone'] ?? '';
                $task = $lineData['task'] ?? '';

                // Als dit een milestone header is (group_milestone_id maar geen group_task_id)
                if ($line->group_milestone_id && !$line->group_task_id && $milestone) {
                    // Format: "Milestone: [naam]"
                    $description = 'Milestone: ' . $milestone;
                }
                // Als dit een task header is (group_task_id is set)
                elseif ($line->group_task_id && $task) {
                    // Format: "[task naam]"
                    $description = $task;
                }
                // Als er wel milestone/task context is maar het is een work item
                elseif ($description && ($milestone || $task)) {
                    // Voeg milestone/task toe aan het begin als ze niet al in description staan
                    if ($milestone && !str_starts_with($description, $milestone)) {
                        $description = $milestone . ($task ? ' > ' . $task : '') . ($description ? ' - ' . $description : '');
                    } elseif ($task && !str_starts_with($description, $task)) {
                        $description = $task . ($description ? ' - ' . $description : '');
                    }
                }

                $line->update([
                    'description' => $description,
                    'quantity' => $lineData['quantity'],
                    'unit_price' => $lineData['unit_price'],
                    'line_total_ex_vat' => $lineData['quantity'] * $lineData['unit_price'],
                    'defer_to_next_month' => $lineData['defer_to_next_month'] ?? false,
                ]);

                // Recalculate VAT for this line
                $line->line_vat_amount = $line->line_total_ex_vat * ($line->vat_rate / 100);
                $line->save();
            }

            // Update alle additional costs lines
            if (isset($validated['costs']) && is_array($validated['costs'])) {
                foreach ($validated['costs'] as $costData) {
                    $costLine = InvoiceLine::findOrFail($costData['id']);

                    // Verify cost line belongs to this invoice and is category='cost'
                    if ($costLine->invoice_id !== $invoice->id || $costLine->category !== 'cost') {
                        throw new \Exception('Invalid cost line ID');
                    }

                    $costLine->update([
                        'description' => $costData['description'],
                        'quantity' => $costData['quantity'],
                        'unit_price' => $costData['unit_price'],
                        'line_total_ex_vat' => $costData['quantity'] * $costData['unit_price'],
                        'defer_to_next_month' => $costData['defer_to_next_month'] ?? false,
                    ]);

                    // Recalculate VAT for this cost line
                    $costLine->line_vat_amount = $costLine->line_total_ex_vat * ($costLine->vat_rate / 100);
                    $costLine->save();

                    // NIEUW: Update ook de gekoppelde ProjectAdditionalCost als deze bestaat
                    if ($costLine->source_type === 'additional_cost' && $costLine->source_id) {
                        $projectCost = ProjectAdditionalCost::find($costLine->source_id);

                        if ($projectCost) {
                            $projectCost->update([
                                'name' => substr($costData['description'], 0, 255), // Eerste 255 chars
                                'description' => $costData['description'],
                                'quantity' => $costData['quantity'],
                                'amount' => $costData['unit_price'], // Unit price in amount field
                            ]);

                            Log::info('ProjectAdditionalCost synced from spreadsheet edit', [
                                'project_cost_id' => $projectCost->id,
                                'invoice_line_id' => $costLine->id,
                                'old_name' => $projectCost->getOriginal('name'),
                                'new_name' => $projectCost->name,
                                'old_quantity' => $projectCost->getOriginal('quantity'),
                                'new_quantity' => $projectCost->quantity,
                                'old_amount' => $projectCost->getOriginal('amount'),
                                'new_amount' => $projectCost->amount,
                            ]);
                        }
                    }
                }
            }

            // Create nieuwe additional costs als InvoiceLine records
            Log::info('Checking new_costs array', [
                'new_costs_isset' => isset($validated['new_costs']),
                'new_costs_is_array' => isset($validated['new_costs']) && is_array($validated['new_costs']),
                'new_costs_count' => isset($validated['new_costs']) ? count($validated['new_costs']) : 0,
                'new_costs_data' => $validated['new_costs'] ?? null,
            ]);

            if (isset($validated['new_costs']) && is_array($validated['new_costs']) && count($validated['new_costs']) > 0) {
                Log::info('Processing new_costs - ENTERING LOOP', ['count' => count($validated['new_costs'])]);

                // Get highest sort_order to append new costs at the end
                $maxSortOrder = InvoiceLine::where('invoice_id', $invoice->id)
                    ->where('category', 'cost')
                    ->max('sort_order') ?? 0;

                foreach ($validated['new_costs'] as $idx => $newCostData) {
                    $lineTotalExVat = $newCostData['quantity'] * $newCostData['unit_price'];
                    $vatAmount = $lineTotalExVat * ($invoice->vat_rate / 100);

                    // Bepaal is_billable op basis van fee_type
                    $feeType = $newCostData['fee_type'] ?? 'in_fee';
                    $isBillable = ($feeType === 'additional'); // additional = true, in_fee = false

                    // Maak eerst de ProjectAdditionalCost aan
                    $projectCost = ProjectAdditionalCost::create([
                        'project_id' => $invoice->project_id,
                        'created_by' => Auth::id(),
                        'name' => substr($newCostData['description'], 0, 255), // Eerste 255 chars voor name
                        'description' => $newCostData['description'],
                        'cost_type' => 'one_time', // Default naar one_time voor invoice costs
                        'fee_type' => $feeType, // in_fee of additional
                        'amount' => $newCostData['unit_price'], // Unit price
                        'quantity' => $newCostData['quantity'],
                        'unit' => 'pieces', // Default unit
                        'calculation_type' => 'quantity_based', // Quantity × Unit Price
                        'start_date' => $invoice->invoice_date ?? now(),
                        'is_active' => true,
                        'category' => 'other', // Default category
                        'auto_invoice' => false,
                        'notes' => 'Created from invoice spreadsheet',
                    ]);

                    Log::info('Created ProjectAdditionalCost from invoice', [
                        'project_id' => $invoice->project_id,
                        'cost_id' => $projectCost->id,
                        'name' => $projectCost->name,
                        'amount' => $lineTotalExVat,
                    ]);

                    // Maak nu de InvoiceLine aan met link naar ProjectAdditionalCost
                    $invoiceLine = InvoiceLine::create([
                        'invoice_id' => $invoice->id,
                        'category' => 'cost',
                        'line_type' => 'hours', // BELANGRIJK: line_type ENUM heeft geen 'cost', gebruik 'hours'
                        'source_type' => 'additional_cost', // Link type
                        'source_id' => $projectCost->id, // Link naar ProjectAdditionalCost
                        'description' => $newCostData['description'],
                        'quantity' => $newCostData['quantity'],
                        'unit' => 'piece',
                        'unit_price' => $newCostData['unit_price'],
                        'line_total_ex_vat' => $lineTotalExVat,
                        'vat_rate' => $invoice->vat_rate,
                        'line_vat_amount' => $vatAmount,
                        'is_billable' => $isBillable, // in_fee = false, additional = true
                        'defer_to_next_month' => $newCostData['defer_to_next_month'] ?? false,
                        'sort_order' => $maxSortOrder + $idx + 1,
                    ]);

                    Log::info('Created InvoiceLine linked to ProjectAdditionalCost', [
                        'invoice_line_id' => $invoiceLine->id,
                        'source_id' => $projectCost->id,
                    ]);
                }
            }

            // Delete verwijderde time entry lines
            if (isset($validated['deleted_line_ids']) && is_array($validated['deleted_line_ids'])) {
                foreach ($validated['deleted_line_ids'] as $lineId) {
                    $line = InvoiceLine::find($lineId);

                    if ($line && $line->invoice_id === $invoice->id && $line->category !== 'cost') {
                        Log::info('Deleting time entry line', [
                            'invoice_id' => $invoice->id,
                            'line_id' => $lineId,
                            'description' => $line->description
                        ]);
                        $line->delete();
                    }
                }
            }

            // Delete verwijderde cost lines
            if (isset($validated['deleted_cost_ids']) && is_array($validated['deleted_cost_ids'])) {
                foreach ($validated['deleted_cost_ids'] as $costId) {
                    $costLine = InvoiceLine::find($costId);

                    if ($costLine && $costLine->invoice_id === $invoice->id && $costLine->category === 'cost') {
                        Log::info('Deleting cost line', [
                            'invoice_id' => $invoice->id,
                            'cost_id' => $costId,
                            'description' => $costLine->description,
                            'source_type' => $costLine->source_type,
                            'source_id' => $costLine->source_id,
                        ]);

                        // Als deze cost line gelinkt is aan een ProjectAdditionalCost, delete die ook
                        if ($costLine->source_type === 'additional_cost' && $costLine->source_id) {
                            $projectCost = ProjectAdditionalCost::find($costLine->source_id);

                            if ($projectCost) {
                                Log::info('Also deleting linked ProjectAdditionalCost', [
                                    'project_cost_id' => $projectCost->id,
                                    'name' => $projectCost->name,
                                    'project_id' => $projectCost->project_id,
                                ]);

                                $projectCost->delete(); // Soft delete
                            }
                        }

                        $costLine->delete();
                    }
                }
            }

            // Recalculate invoice totals
            $this->recalculateInvoiceTotals($invoice);

            DB::commit();

            // Return JSON response for AJAX
            return response()->json([
                'success' => true,
                'message' => 'Invoice updated successfully from spreadsheet.',
                'redirect' => route('invoices.edit', $invoice)
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating invoice from spreadsheet', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error updating invoice: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Reorder invoice lines (drag & drop in spreadsheet view)
     */
    public function reorderLines(Request $request, Invoice $invoice)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Only administrators can reorder invoice lines.'
            ], 403);
        }

        // Company isolation
        if (Auth::user()->role !== 'super_admin' && $invoice->invoicing_company_id !== Auth::user()->company_id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. You can only edit invoices from your own company.'
            ], 403);
        }

        // Alleen draft invoices kunnen bewerkt worden
        if ($invoice->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Only draft invoices can be reordered.'
            ], 400);
        }

        // Validatie
        $validated = $request->validate([
            'line_ids' => 'required|array|min:1',
            'line_ids.*' => 'integer|exists:invoice_lines,id',
        ]);

        try {
            DB::beginTransaction();

            // Update sort_order voor alle lines in de nieuwe volgorde
            foreach ($validated['line_ids'] as $index => $lineId) {
                InvoiceLine::where('id', $lineId)
                    ->where('invoice_id', $invoice->id) // Security: alleen lines van deze invoice
                    ->update(['sort_order' => $index + 1]);
            }

            DB::commit();

            Log::info('Invoice lines reordered', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'lines_count' => count($validated['line_ids']),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Invoice lines reordered successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Error reordering invoice lines', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error reordering invoice lines: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reorder additional cost lines (drag & drop in spreadsheet view)
     */
    public function reorderCosts(Request $request, Invoice $invoice)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Only administrators can reorder costs.'
            ], 403);
        }

        // Company isolation
        if (Auth::user()->role !== 'super_admin' && $invoice->invoicing_company_id !== Auth::user()->company_id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. You can only edit invoices from your own company.'
            ], 403);
        }

        // Alleen draft invoices kunnen bewerkt worden
        if ($invoice->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Only draft invoices can be reordered.'
            ], 400);
        }

        // Validatie
        $validated = $request->validate([
            'cost_ids' => 'required|array|min:1',
            'cost_ids.*' => 'integer|exists:invoice_lines,id',
        ]);

        try {
            DB::beginTransaction();

            // Update sort_order voor alle cost lines in de nieuwe volgorde
            foreach ($validated['cost_ids'] as $index => $costId) {
                InvoiceLine::where('id', $costId)
                    ->where('invoice_id', $invoice->id) // Security: alleen lines van deze invoice
                    ->where('category', 'cost') // Extra security: alleen cost lines
                    ->update(['sort_order' => $index + 1]);
            }

            DB::commit();

            Log::info('Invoice cost lines reordered', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'costs_count' => count($validated['cost_ids']),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Additional costs reordered successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Error reordering invoice cost lines', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error reordering costs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show finalize view (Activity Report layout)
     */
    public function finalizeView(Invoice $invoice)
    {
        // Authorization check - admin en super_admin kunnen ALLE facturen zien
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators and project managers can finalize invoices.');
        }

        // Company isolation - ALLEEN voor project_manager
        if (Auth::user()->role === 'project_manager') {
            if ($invoice->project && $invoice->project->company_id !== Auth::user()->company_id) {
                abort(403, 'Access denied. Project managers can only finalize invoices from their own company.');
            }
        }

        // Load relationships
        $invoice->load([
            'project.customer',
            'project.companyRelation',
            'lines.milestone',
            'lines.task',
            'lines.timeEntries'
        ]);

        return view('invoices.finalize', compact('invoice'));
    }

    /**
     * Update single field in invoice line (inline editing)
     */
    public function updateLineField(Request $request, $lineId)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Only administrators and project managers can edit invoice lines.'
            ], 403);
        }

        $validated = $request->validate([
            'field' => 'required|string|in:description,quantity,unit_price',
            'value' => 'required'
        ]);

        try {
            $line = InvoiceLine::findOrFail($lineId);

            // Company isolation check
            if (Auth::user()->role !== 'super_admin') {
                $invoice = $line->invoice;
                if ($invoice->project && $invoice->project->company_id !== Auth::user()->company_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Access denied. You can only edit invoice lines from your own company.'
                    ], 403);
                }
            }

            // Check if invoice is still editable
            if ($line->invoice->status === 'finalized') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot edit finalized invoice.'
                ], 400);
            }

            // Update field
            $field = $validated['field'];
            $value = $validated['value'];

            if ($field === 'quantity' || $field === 'unit_price') {
                // Convert to decimal
                $value = (float) str_replace(',', '.', $value);
            }

            $line->$field = $value;

            // Recalculate line totals if quantity or price changed
            if ($field === 'quantity' || $field === 'unit_price') {
                $line->line_total_ex_vat = $line->quantity * $line->unit_price;
                $line->line_vat_amount = $line->line_total_ex_vat * ($line->vat_rate / 100);
            }

            $line->save();

            Log::info('Invoice line field updated', [
                'line_id' => $line->id,
                'invoice_id' => $line->invoice_id,
                'field' => $field,
                'new_value' => $value,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Field updated successfully.',
                'line' => $line
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating invoice line field', [
                'line_id' => $lineId,
                'field' => $validated['field'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error updating field: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Finalize confirm - Mark invoice as finalized
     */
    public function finalizeConfirm(Invoice $invoice)
    {
        // Authorization check - admin en super_admin kunnen ALLE facturen finaliseren
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators and project managers can finalize invoices.');
        }

        // Company isolation - ALLEEN voor project_manager
        if (Auth::user()->role === 'project_manager') {
            if ($invoice->project && $invoice->project->company_id !== Auth::user()->company_id) {
                abort(403, 'Access denied. Project managers can only finalize invoices from their own company.');
            }
        }

        // Check if already finalized
        if ($invoice->status === 'finalized') {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Invoice is already finalized.');
        }

        try {
            DB::beginTransaction();

            // Update invoice status
            $invoice->status = 'finalized';
            $invoice->is_editable = false;
            $invoice->finalized_at = now();
            $invoice->finalized_by = Auth::id();
            $invoice->save();

            // Recalculate totals (voor de zekerheid)
            $this->recalculateInvoiceTotals($invoice);

            DB::commit();

            Log::info('Invoice finalized', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'finalized_by' => Auth::id(),
            ]);

            return redirect()->route('reports.index')
                ->with('success', 'Report has been finalized successfully! The report is now locked for editing.');

        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Error finalizing invoice', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Error finalizing invoice: ' . $e->getMessage());
        }
    }
}