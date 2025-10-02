<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ClaudeAIService;
use App\Models\Project;
use App\Models\TimeEntry;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Carbon\Carbon;

class InvoiceAITestController extends Controller
{
    protected ClaudeAIService $aiService;
    
    public function __construct(ClaudeAIService $aiService)
    {
        $this->aiService = $aiService;
    }
    
    /**
     * Show test page for AI invoice description bundling
     */
    public function index()
    {
        // Get projects with time entries
        $projects = Project::whereHas('timeEntries', function($q) {
            $q->where('status', 'approved')
              ->where('entry_date', '>=', now()->subMonth());
        })
        ->with(['customer', 'companyRelation'])
        ->orderBy('name')
        ->get();
        
        return view('invoices.ai-test', compact('projects'));
    }
    
    /**
     * Test AI summarization for a specific project
     */
    public function testSummarization(Request $request, Project $project)
    {
        $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'consolidation_level' => 'required|in:hierarchical,smart,milestone,task,none'
        ]);
        
        $periodStart = Carbon::parse($request->period_start);
        $periodEnd = Carbon::parse($request->period_end);
        
        // Get time entries for the period
        $timeEntries = TimeEntry::where('project_id', $project->id)
            ->where('status', 'approved')
            ->whereBetween('entry_date', [$periodStart, $periodEnd])
            ->with(['user', 'milestone', 'task', 'subtask'])
            ->orderBy('entry_date')
            ->get();
        
        if ($timeEntries->isEmpty()) {
            return back()->with('error', 'No approved time entries found for this period.');
        }
        
        // Prepare time entries for AI analysis
        $entriesData = $timeEntries->map(function($entry) {
            return [
                'id' => $entry->id,
                'entry_date' => $entry->entry_date->format('Y-m-d'),
                'hours' => $entry->hours + ($entry->minutes / 60),
                'description' => $entry->description,
                'user' => ['name' => $entry->user->name ?? 'Unknown'],
                'milestone' => $entry->milestone ? $entry->milestone->name : null,
                'task' => $entry->task ? $entry->task->name : null,
                'subtask' => $entry->subtask ? $entry->subtask->name : null,
                'hourly_rate' => $entry->hourly_rate_used ?? $project->default_hourly_rate
            ];
        })->toArray();
        
        // Get AI summary based on consolidation level
        $results = [];
        
        switch ($request->consolidation_level) {
            case 'hierarchical':
                // Create hierarchical structure with milestones > tasks > descriptions
                $results['hierarchical_summary'] = $this->createHierarchicalStructure($timeEntries, $project);
                break;
                
            case 'smart':
                // AI groups similar activities intelligently
                $results['ai_summary'] = $this->aiService->summarizeTimeEntriesForInvoice(
                    $entriesData,
                    "Project: {$project->name}, Customer: {$project->customer->name}"
                );
                
                // Also get consolidated invoice lines suggestion
                $invoiceLines = $this->prepareInvoiceLines($timeEntries, $project);
                $consolidatedResult = $this->aiService->consolidateInvoiceLines($invoiceLines);
                
                // Add debug logging
                \Log::info('AI Consolidated Lines Result:', [
                    'success' => $consolidatedResult['success'] ?? false,
                    'data' => $consolidatedResult['data'] ?? null,
                    'invoice_lines_count' => count($invoiceLines)
                ]);
                
                // If AI consolidation failed or returned empty, create a simple fallback
                if (!isset($consolidatedResult['data']['consolidated_groups']) || empty($consolidatedResult['data']['consolidated_groups'])) {
                    // Group time entries by milestone/task for better structure
                    $groupedEntries = [];
                    foreach ($timeEntries as $entry) {
                        // Create a hierarchical group key
                        $groupKey = '';
                        $groupParts = [];
                        
                        if ($entry->milestone) {
                            $groupParts[] = $entry->milestone->name;
                        }
                        if ($entry->task) {
                            $groupParts[] = $entry->task->name;
                        }
                        
                        // If we have structure, use it; otherwise use "General Work"
                        $groupKey = !empty($groupParts) ? implode(' > ', $groupParts) : 'General Work';
                        
                        if (!isset($groupedEntries[$groupKey])) {
                            $groupedEntries[$groupKey] = [
                                'entries' => [],
                                'total_hours' => 0,
                                'descriptions' => [],
                                'milestone' => $entry->milestone ? $entry->milestone->name : null,
                                'task' => $entry->task ? $entry->task->name : null
                            ];
                        }
                        $groupedEntries[$groupKey]['entries'][] = $entry;
                        $groupedEntries[$groupKey]['total_hours'] += $entry->hours + ($entry->minutes / 60);
                        if (!empty($entry->description)) {
                            $groupedEntries[$groupKey]['descriptions'][] = $entry->description;
                        }
                    }
                    
                    // Create consolidated groups manually with better structure
                    $consolidatedGroups = [];
                    foreach ($groupedEntries as $groupName => $groupData) {
                        $uniqueDescriptions = array_unique($groupData['descriptions']);
                        
                        // Create a better combined description
                        $combinedDesc = '';
                        if (count($uniqueDescriptions) > 0) {
                            // Take first 5 unique descriptions for better detail
                            $selectedDescs = array_slice($uniqueDescriptions, 0, 5);
                            if (count($selectedDescs) > 3) {
                                // If many descriptions, group them with bullets
                                $combinedDesc = implode('; ', $selectedDescs);
                            } else {
                                $combinedDesc = implode(', ', $selectedDescs);
                            }
                        }
                        
                        $consolidatedGroups[] = [
                            'group_name' => $groupName,
                            'combined_description' => $combinedDesc,
                            'total_hours' => round($groupData['total_hours'], 2),
                            'suggested_pricing' => '€' . round($groupData['total_hours'] * ($project->default_hourly_rate ?? 75), 2),
                            'entry_count' => count($groupData['entries'])
                        ];
                    }
                    
                    $consolidatedResult = [
                        'success' => true,
                        'data' => [
                            'consolidated_groups' => $consolidatedGroups
                        ]
                    ];
                }
                
                $results['consolidated_lines'] = $consolidatedResult;
                break;
                
            case 'milestone':
                // Group by milestone first, then summarize each group
                $grouped = $this->groupByMilestone($entriesData);
                $results['grouped_summaries'] = [];
                
                foreach ($grouped as $milestoneId => $entries) {
                    $milestoneName = $entries[0]['milestone'] ?? 'General Work';
                    $results['grouped_summaries'][$milestoneName] = $this->aiService->summarizeTimeEntriesForInvoice(
                        $entries,
                        "Milestone: $milestoneName"
                    );
                }
                break;
                
            case 'task':
                // Group by task, then summarize
                $grouped = $this->groupByTask($entriesData);
                $results['grouped_summaries'] = [];
                
                foreach ($grouped as $taskKey => $entries) {
                    $taskName = $entries[0]['task'] ?? 'General Tasks';
                    $results['grouped_summaries'][$taskName] = $this->aiService->summarizeTimeEntriesForInvoice(
                        $entries,
                        "Task: $taskName"
                    );
                }
                break;
                
            case 'none':
                // Show original entries without AI processing
                $results['original_entries'] = $entriesData;
                break;
        }
        
        // Generate invoice description suggestion
        if ($request->consolidation_level !== 'none') {
            $workSummary = $results['ai_summary']['data'] ?? [];
            $descriptionResult = $this->aiService->generateInvoiceDescription(
                $project->name,
                $workSummary,
                "{$periodStart->format('M d')} - {$periodEnd->format('M d, Y')}"
            );
            
            // Extract the actual description string from the result
            if (isset($descriptionResult['data']['invoice_description'])) {
                $results['invoice_description'] = $descriptionResult['data']['invoice_description'];
            } elseif (isset($descriptionResult['data']) && is_string($descriptionResult['data'])) {
                $results['invoice_description'] = $descriptionResult['data'];
            } else {
                $results['invoice_description'] = "Services rendered for {$project->name}";
            }
        }
        
        // Calculate totals
        $totalHours = $timeEntries->sum('hours') + ($timeEntries->sum('minutes') / 60);
        $averageRate = $timeEntries->avg('hourly_rate_used') ?? $project->default_hourly_rate;
        $estimatedAmount = $totalHours * $averageRate;
        
        return view('invoices.ai-test-results', [
            'project' => $project,
            'periodStart' => $periodStart,
            'periodEnd' => $periodEnd,
            'timeEntries' => $timeEntries,
            'results' => $results,
            'consolidationLevel' => $request->consolidation_level,
            'totalHours' => round($totalHours, 2),
            'averageRate' => round($averageRate, 2),
            'estimatedAmount' => round($estimatedAmount, 2),
            'entryCount' => $timeEntries->count()
        ]);
    }
    
    /**
     * Apply AI suggestions to create draft invoice
     */
    public function applyToInvoice(Request $request, Project $project)
    {
        $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date',
            'consolidated_description' => 'required|string',
            'line_items' => 'required|array'
        ]);
        
        try {
            // For now, create a simple invoice without the full AI service
            // to avoid potential issues with the service
            
            $invoice = new \App\Models\Invoice();
            $invoice->project_id = $project->id;
            $invoice->customer_id = $project->customer_id;
            $invoice->invoicing_company_id = $project->company_id;
            $invoice->status = 'draft';
            $invoice->is_editable = true;
            $invoice->draft_name = "AI Test - {$project->name} - " . Carbon::parse($request->period_start)->format('M Y');
            $invoice->invoice_date = now();
            $invoice->due_date = now()->addDays(30);
            $invoice->period_start = Carbon::parse($request->period_start);
            $invoice->period_end = Carbon::parse($request->period_end);
            $invoice->billing_type = $project->billing_type ?? 'hourly';
            $invoice->vat_rate = $project->vat_rate ?? 21;
            $invoice->monthly_budget = $project->monthly_fee ?? 0;
            $invoice->ai_generated = true;
            $invoice->ai_generated_at = now();
            $invoice->ai_confidence_score = 0.85;
            $invoice->created_by = auth()->id();
            
            // Set default values for required fields
            $invoice->fee_balance_previous = 0;
            $invoice->fee_balance_current = $project->monthly_fee ?? 0;
            $invoice->fee_performed = 0;
            $invoice->fee_balance_new = $project->monthly_fee ?? 0;
            $invoice->additional_costs_in_fee = 0;
            $invoice->additional_costs_outside_fee = 0;
            
            $invoice->save();
            
            // Create invoice lines from the consolidated results
            if (!empty($request->line_items)) {
                foreach ($request->line_items as $index => $item) {
                    $line = new \App\Models\InvoiceLine();
                    $line->invoice_id = $invoice->id;
                    $line->line_type = 'hours';
                    
                    // Add prefixes for display detection but store clean descriptions
                    $description = $item['description'] ?? $request->consolidated_description;
                    if (isset($item['line_prefix'])) {
                        if ($item['line_prefix'] === 'task') {
                            $description = '→ ' . $description;
                        } elseif ($item['line_prefix'] === 'description') {
                            $description = '• ' . $description;
                        }
                    }
                    $line->description = $description;
                    $line->quantity = $item['quantity'] ?? 1;
                    $line->unit = $item['unit'] ?? 'hours';
                    $line->unit_price = $item['unit_price'] ?? $project->default_hourly_rate ?? 75;
                    $line->unit_price_ex_vat = $line->unit_price;
                    $line->line_total_ex_vat = $line->quantity * $line->unit_price;
                    $line->vat_rate = $invoice->vat_rate;
                    $line->line_vat_amount = $line->line_total_ex_vat * ($invoice->vat_rate / 100);
                    $line->sort_order = $index + 1;
                    $line->save();
                }
            } else {
                // Create at least one line with the consolidated description
                $line = new \App\Models\InvoiceLine();
                $line->invoice_id = $invoice->id;
                $line->line_type = 'hours';
                $line->description = $request->consolidated_description;
                $line->quantity = 1;
                $line->unit = 'hours';
                $line->unit_price = $project->default_hourly_rate ?? 75;
                $line->unit_price_ex_vat = $line->unit_price;
                $line->line_total_ex_vat = $line->unit_price;
                $line->vat_rate = $invoice->vat_rate;
                $line->line_vat_amount = $line->line_total_ex_vat * ($invoice->vat_rate / 100);
                $line->sort_order = 1;
                $line->save();
            }
            
            // Update invoice totals manually instead of using recalculateTotals
            $subtotal = 0;
            $vatAmount = 0;
            
            if (!empty($request->line_items)) {
                foreach ($request->line_items as $item) {
                    $lineTotal = ($item['quantity'] ?? 1) * ($item['unit_price'] ?? $project->default_hourly_rate ?? 75);
                    $subtotal += $lineTotal;
                    $vatAmount += $lineTotal * ($invoice->vat_rate / 100);
                }
            } else {
                $subtotal = $project->default_hourly_rate ?? 75;
                $vatAmount = $subtotal * ($invoice->vat_rate / 100);
            }
            
            $invoice->subtotal_ex_vat = $subtotal;
            $invoice->vat_amount = $vatAmount;
            $invoice->total_inc_vat = $subtotal + $vatAmount;
            $invoice->save();
            
            // Redirect to the edit page for the new invoice
            return redirect()->route('invoices.edit', $invoice)
                ->with('success', 'AI Test Invoice created successfully! Review and adjust as needed before finalizing.');
                
        } catch (\Exception $e) {
            \Log::error('Failed to apply AI invoice - Detailed error: ' . $e->getMessage() . ' - Line: ' . $e->getLine() . ' - File: ' . $e->getFile());
            
            return back()->with('error', 'Failed to create invoice: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Create direct invoice from test (simplified version)
     */
    public function createDirectInvoice(Request $request, Project $project)
    {
        try {
            // Create a basic invoice
            $invoice = new \App\Models\Invoice();
            $invoice->project_id = $project->id;
            $invoice->customer_id = $project->customer_id;
            $invoice->invoicing_company_id = $project->company_id;
            $invoice->status = 'draft';
            $invoice->is_editable = true;
            $invoice->draft_name = "AI Test Invoice - {$project->name}";
            $invoice->invoice_date = now();
            $invoice->due_date = now()->addDays(30);
            $invoice->period_start = now()->startOfMonth();
            $invoice->period_end = now()->endOfMonth();
            $invoice->billing_type = 'hourly';
            $invoice->vat_rate = 21;
            $invoice->monthly_budget = $project->monthly_fee ?? 0;
            $invoice->ai_generated = true;
            $invoice->ai_generated_at = now();
            $invoice->created_by = auth()->id();
            
            // Initialize fee balance fields
            $invoice->fee_balance_previous = 0;
            $invoice->fee_balance_current = $project->monthly_fee ?? 0;
            $invoice->fee_performed = 0;
            $invoice->fee_balance_new = $project->monthly_fee ?? 0;
            $invoice->additional_costs_in_fee = 0;
            $invoice->additional_costs_outside_fee = 0;
            
            $invoice->save();
            
            // Add a sample line
            $line = new \App\Models\InvoiceLine();
            $line->invoice_id = $invoice->id;
            $line->line_type = 'hours';
            $line->fee_type = 'in_fee';
            $line->description = 'AI Generated - Development and consulting services';
            $line->quantity = 10;
            $line->unit = 'hours';
            $line->unit_price = 75;
            $line->unit_price_ex_vat = 75;
            $line->line_total_ex_vat = 750;
            $line->vat_rate = 21;
            $line->line_vat_amount = 157.50;
            $line->is_ai_generated = true;
            $line->sort_order = 1;
            $line->save();
            
            // Update totals
            $invoice->subtotal_ex_vat = 750;
            $invoice->vat_amount = 157.50;
            $invoice->total_inc_vat = 907.50;
            $invoice->save();
            
            return redirect()->route('invoices.edit', $invoice)
                ->with('success', 'Test invoice created successfully! You can now edit it.');
                
        } catch (\Exception $e) {
            return back()->with('error', 'Error creating invoice: ' . $e->getMessage());
        }
    }
    
    /**
     * Quick create invoice - completely new method
     */
    public function quickCreate(Project $project)
    {
        // Direct invoice creation bypassing all complexity
        \DB::beginTransaction();
        
        try {
            $invoice = \App\Models\Invoice::create([
                'project_id' => $project->id,
                'customer_id' => $project->customer_id,
                'invoicing_company_id' => $project->company_id,
                'status' => 'draft',
                'is_editable' => true,
                'draft_name' => 'Quick AI Invoice - ' . now()->format('Y-m-d'),
                'invoice_date' => now(),
                'due_date' => now()->addDays(30),
                'period_start' => now()->startOfMonth(),
                'period_end' => now()->endOfMonth(),
                'billing_type' => 'hourly',
                'vat_rate' => 21,
                'monthly_budget' => $project->monthly_fee ?? 0,
                'created_by' => auth()->id(),
                'ai_generated' => true,
                'ai_generated_at' => now(),
                'ai_confidence_score' => 0.85,
                'fee_balance_previous' => 0,
                'fee_balance_current' => $project->monthly_fee ?? 0,
                'fee_performed' => 0,
                'fee_balance_new' => $project->monthly_fee ?? 0,
                'additional_costs_in_fee' => 0,
                'additional_costs_outside_fee' => 0,
                'subtotal_ex_vat' => 0,
                'vat_amount' => 0,
                'total_inc_vat' => 0
            ]);
            
            \DB::commit();
            
            // Direct redirect - no views, no complications
            return redirect('/invoices/' . $invoice->id . '/edit')
                ->with('success', 'Quick invoice created! ID: ' . $invoice->id);
                
        } catch (\Exception $e) {
            \DB::rollback();
            // Return plain text error to see what's happening
            return response('ERROR: ' . $e->getMessage() . ' at line ' . $e->getLine(), 500)
                ->header('Content-Type', 'text/plain');
        }
    }
    
    /**
     * Create hierarchical structure for invoice
     */
    protected function createHierarchicalStructure($timeEntries, $project)
    {
        $structure = [];
        $hourlyRate = $project->default_hourly_rate ?? 75;
        
        // Group by milestone > task > descriptions
        foreach ($timeEntries as $entry) {
            $milestoneKey = $entry->milestone ? $entry->milestone->name : 'General Work';
            $taskKey = $entry->task ? $entry->task->name : 'General Tasks';
            
            if (!isset($structure[$milestoneKey])) {
                $structure[$milestoneKey] = [
                    'name' => $milestoneKey,
                    'tasks' => [],
                    'total_hours' => 0,
                    'total_amount' => 0
                ];
            }
            
            if (!isset($structure[$milestoneKey]['tasks'][$taskKey])) {
                $structure[$milestoneKey]['tasks'][$taskKey] = [
                    'name' => $taskKey,
                    'descriptions' => [],
                    'consolidated_descriptions' => [],
                    'total_hours' => 0,
                    'total_amount' => 0
                ];
            }
            
            // Add entry details
            $hours = $entry->hours + ($entry->minutes / 60);
            $rate = $entry->hourly_rate_used ?? $hourlyRate;
            $amount = $hours * $rate;
            
            $entryData = [
                'description' => $entry->description,
                'hours' => round($hours, 2),
                'rate' => $rate,
                'amount' => round($amount, 2),
                'date' => $entry->entry_date->format('Y-m-d'),
                'user' => $entry->user->name ?? 'Unknown'
            ];
            
            $structure[$milestoneKey]['tasks'][$taskKey]['descriptions'][] = $entryData;
            $structure[$milestoneKey]['tasks'][$taskKey]['total_hours'] += $hours;
            $structure[$milestoneKey]['tasks'][$taskKey]['total_amount'] += $amount;
            $structure[$milestoneKey]['total_hours'] += $hours;
            $structure[$milestoneKey]['total_amount'] += $amount;
            
            // Add to consolidated descriptions (unique)
            if (!empty($entry->description) && 
                !in_array($entry->description, $structure[$milestoneKey]['tasks'][$taskKey]['consolidated_descriptions'])) {
                $structure[$milestoneKey]['tasks'][$taskKey]['consolidated_descriptions'][] = $entry->description;
            }
        }
        
        // Convert to indexed array for view
        $milestones = [];
        foreach ($structure as $milestone) {
            $tasks = [];
            foreach ($milestone['tasks'] as $task) {
                $task['total_hours'] = round($task['total_hours'], 2);
                $task['total_amount'] = round($task['total_amount'], 2);
                $tasks[] = $task;
            }
            $milestone['tasks'] = $tasks;
            $milestone['total_hours'] = round($milestone['total_hours'], 2);
            $milestone['total_amount'] = round($milestone['total_amount'], 2);
            $milestones[] = $milestone;
        }
        
        return [
            'success' => true,
            'data' => [
                'milestones' => $milestones
            ]
        ];
    }
    
    /**
     * Prepare invoice lines for consolidation
     */
    protected function prepareInvoiceLines($timeEntries, $project)
    {
        $lines = [];
        
        foreach ($timeEntries as $entry) {
            $lines[] = [
                'description' => $this->buildDescription($entry),
                'quantity' => $entry->hours + ($entry->minutes / 60),
                'unit' => 'hours',
                'unit_price' => $entry->hourly_rate_used ?? $project->default_hourly_rate
            ];
        }
        
        return $lines;
    }
    
    /**
     * Build description from time entry
     */
    protected function buildDescription($entry)
    {
        $parts = [];
        
        if ($entry->milestone) {
            $parts[] = $entry->milestone->name;
        }
        if ($entry->task) {
            $parts[] = $entry->task->name;
        }
        if ($entry->subtask) {
            $parts[] = $entry->subtask->name;
        }
        
        $prefix = !empty($parts) ? implode(' > ', $parts) . ': ' : '';
        
        return $prefix . $entry->description;
    }
    
    /**
     * Group entries by milestone
     */
    protected function groupByMilestone($entries)
    {
        $grouped = [];
        
        foreach ($entries as $entry) {
            $key = $entry['milestone'] ?? 'no-milestone';
            if (!isset($grouped[$key])) {
                $grouped[$key] = [];
            }
            $grouped[$key][] = $entry;
        }
        
        return $grouped;
    }
    
    /**
     * Group entries by task
     */
    protected function groupByTask($entries)
    {
        $grouped = [];
        
        foreach ($entries as $entry) {
            $key = ($entry['milestone'] ?? 'general') . '::' . ($entry['task'] ?? 'no-task');
            if (!isset($grouped[$key])) {
                $grouped[$key] = [];
            }
            $grouped[$key][] = $entry;
        }
        
        return $grouped;
    }
}