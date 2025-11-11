<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Project;
use App\Models\TimeEntry;
use App\Models\ProjectAdditionalCost;
use App\Models\ProjectMonthlyFee;
use App\Models\ProjectMilestone;
use App\Models\ProjectTask;
use App\Models\ProjectSubtask;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class InvoiceGenerationService
{
    protected Project $project;
    protected Carbon $periodStart;
    protected Carbon $periodEnd;
    protected ?Invoice $previousInvoice = null;
    protected array $consolidatedLines = [];
    protected float $previousMonthRemaining = 0;
    protected float $monthlyBudget = 0;
    protected float $totalBudget = 0;

    /**
     * Generate invoice for a project based on billing type
     */
    public function generateForProject(Project $project, Carbon $periodStart, Carbon $periodEnd): Invoice
    {
        $this->project = $project;
        $this->periodStart = $periodStart;
        $this->periodEnd = $periodEnd;
        
        // Get previous invoice for rollover calculation
        // KRITIEK: Voor recurring series zoeken over ALLE projecten in de serie!
        if ($project->recurring_series_id) {
            // Zoek in ALLE projecten met dezelfde recurring_series_id
            $seriesProjectIds = \App\Models\Project::where('recurring_series_id', $project->recurring_series_id)
                ->pluck('id')
                ->toArray();

            $this->previousInvoice = Invoice::whereIn('project_id', $seriesProjectIds)
                ->where('status', '!=', 'cancelled')
                ->where('period_end', '<', $periodStart)
                ->orderBy('period_end', 'desc')
                ->first();
        } else {
            // Fallback: alleen dit project (voor oude projecten zonder series)
            $this->previousInvoice = Invoice::where('project_id', $project->id)
                ->where('status', '!=', 'cancelled')
                ->where('period_end', '<', $periodStart)
                ->orderBy('period_end', 'desc')
                ->first();
        }
        
        // Calculate budget
        $this->calculateBudget();
        
        // Create invoice based on billing type
        return match($project->billing_frequency) {
            'monthly' => $this->generateMonthlyInvoice(),
            'quarterly' => $this->generateQuarterlyInvoice(),
            'milestone' => $this->generateMilestoneInvoice(),
            'project_completion' => $this->generateProjectCompletionInvoice(),
            'custom', 'manual' => $this->generateCustomInvoice(),
            default => $this->generateMonthlyInvoice()
        };
    }
    
    /**
     * Calculate budget including rollover
     */
    protected function calculateBudget(): void
    {
        // Get monthly budget from project
        $this->monthlyBudget = $this->project->monthly_fee ?? 0;
        
        // Get rollover from previous invoice
        if ($this->previousInvoice) {
            $this->previousMonthRemaining = $this->previousInvoice->next_month_rollover ?? 0;
        }
        
        // Calculate total available budget
        $this->totalBudget = $this->monthlyBudget + $this->previousMonthRemaining;
    }
    
    /**
     * Generate monthly invoice
     */
    protected function generateMonthlyInvoice(): Invoice
    {
        DB::beginTransaction();
        
        try {
            // Determine which template to use
            $templateId = $this->determineInvoiceTemplate();
            
            // Create invoice
            $invoice = Invoice::create([
                'project_id' => $this->project->id,
                'invoicing_company_id' => $this->project->main_invoicing_company_id ?? $this->project->company_id,
                'customer_id' => $this->project->customer_id,
                'invoice_template_id' => $templateId,
                'created_by' => Auth::id(),
                'invoice_number' => $this->generateInvoiceNumber(),
                'status' => 'draft',
                'billing_type' => 'monthly',
                'invoice_date' => now(),
                'due_date' => now()->addDays(30),
                'period_start' => $this->periodStart,
                'period_end' => $this->periodEnd,
                'previous_month_remaining' => $this->previousMonthRemaining,
                'monthly_budget' => $this->monthlyBudget,
                'total_budget' => $this->totalBudget,
                'vat_rate' => $this->project->vat_rate ?? 21,
            ]);
            
            // Generate line items
            $this->generateTimeEntryLines($invoice); // Includes both current period + deferred items
            $this->generateServicePackageLines($invoice);
            $this->generateAdditionalCostLines($invoice);
            // NOTE: Deferred items zijn nu geïntegreerd in generateTimeEntryLines() voor correcte sorting

            // Calculate totals
            $this->calculateInvoiceTotals($invoice);
            
            DB::commit();
            
            return $invoice;
            
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
    
    /**
     * Generate time entry lines (consolidated by milestone/task/subtask)
     */
    protected function generateTimeEntryLines(Invoice $invoice): void
    {
        // Get approved time entries for current period
        // KRITIEK: Sorteer op milestone.sort_order en task.sort_order, NIET op IDs!
        $currentPeriodEntries = TimeEntry::where('time_entries.project_id', $this->project->id)
            ->where('time_entries.status', 'approved')
            ->whereNull('time_entries.invoice_id')
            ->where('time_entries.was_deferred', false)
            ->whereBetween('time_entries.entry_date', [$this->periodStart, $this->periodEnd])
            ->select('time_entries.*')
            ->get();

        // Get deferred entries from previous periods (for recurring series)
        $deferredEntries = collect();
        if (in_array($this->project->billing_frequency, ['monthly', 'quarterly']) && $this->project->recurring_series_id) {
            $seriesProjectIds = \App\Models\Project::where('recurring_series_id', $this->project->recurring_series_id)
                ->pluck('id')
                ->toArray();

            $deferredEntries = TimeEntry::where('time_entries.status', 'approved')
                ->where('time_entries.was_deferred', true)
                ->whereNull('time_entries.invoice_id')
                ->whereIn('time_entries.project_id', $seriesProjectIds)
                ->select('time_entries.*')
                ->get();

            // Map deferred entries naar huidige project milestone/task
            foreach ($deferredEntries as $entry) {
                // Bewaar originele IDs in metadata (niet als model properties!)
                $originalMilestoneId = $entry->project_milestone_id;
                $originalTaskId = $entry->project_task_id;

                // Map naar nieuwe project milestone/task
                $newMilestoneId = $this->findMatchingMilestone($originalMilestoneId);
                $newTaskId = $this->findMatchingTask($originalTaskId, $newMilestoneId);

                // Sla mapping op in een cache array zodat we later kunnen updaten
                if (!isset($entry->_defer_mapping)) {
                    $entry->_defer_mapping = [
                        'original_milestone_id' => $originalMilestoneId,
                        'original_task_id' => $originalTaskId,
                        'new_milestone_id' => $newMilestoneId,
                        'new_task_id' => $newTaskId,
                        'is_deferred_item' => true,
                    ];
                }

                // Overschrijf milestone/task IDs voor grouping
                $entry->project_milestone_id = $newMilestoneId;
                $entry->project_task_id = $newTaskId;

                // Parse defer history from defer_reason
                if ($entry->defer_reason) {
                    preg_match('/invoice ([A-Z0-9-]+).*\(([^)]+)\)/', $entry->defer_reason, $matches);
                    $entry->_defer_history = [
                        'from_invoice_number' => $matches[1] ?? 'unknown',
                        'from_period' => $matches[2] ?? 'previous month',
                    ];
                }
            }
        }

        // Merge current + deferred entries en sorteer op milestone/task
        $timeEntries = $currentPeriodEntries->merge($deferredEntries);

        // Load relationships
        $timeEntries->load(['milestone', 'task', 'user']);

        // Sorteer: eerst milestone sort_order, dan task sort_order, dan datum
        // KRITIEK: Gebruik chained sortBy ipv array van closures
        $timeEntries = $timeEntries->sortBy(function($entry) {
            return $entry->entry_date;
        })->sortBy(function($entry) {
            return $entry->task ? $entry->task->sort_order : 999999;
        })->sortBy(function($entry) {
            return $entry->milestone ? $entry->milestone->sort_order : 999999;
        })->values();

        $sortOrder = 10;
        $currentMilestone = null;
        $currentTask = null;
        $taskEntries = [];

        // Group entries hierarchically
        foreach ($timeEntries as $entry) {
            // Check if we need to create milestone header
            if ($currentMilestone !== $entry->project_milestone_id) {
                // Finalize previous task if it exists
                if ($currentTask !== null) {
                    $this->createTaskLines($invoice, $currentTask, $taskEntries, $sortOrder);
                    $taskEntries = [];
                }

                // Start new milestone - create header immediately
                $currentMilestone = $entry->project_milestone_id;
                $currentTask = null;
                $this->createMilestoneHeader($invoice, $entry->milestone, $sortOrder);
            }

            // Check if we need to create task header
            if ($currentTask !== $entry->project_task_id) {
                // Finalize previous task if it exists
                if ($currentTask !== null) {
                    $this->createTaskLines($invoice, $currentTask, $taskEntries, $sortOrder);
                    $taskEntries = [];
                }

                // Start new task
                $currentTask = $entry->project_task_id;
                if ($entry->task) {
                    $this->createTaskHeader($invoice, $entry->task, $sortOrder);
                }
            }

            // Add entry to current task
            $taskEntries[] = $entry;
        }

        // Finalize last task
        if ($currentTask !== null) {
            $this->createTaskLines($invoice, $currentTask, $taskEntries, $sortOrder);
        }
    }

    protected function createMilestoneHeader(Invoice $invoice, $milestone, &$sortOrder): void
    {
        if (!$milestone) return;

        InvoiceLine::create([
            'invoice_id' => $invoice->id,
            'source_type' => 'milestone_header',
            'group_milestone_id' => $milestone->id,
            'description' => 'Milestone: ' . $milestone->name,
            'quantity' => 0,
            'unit' => 'hours',
            'unit_price' => 0,
            'unit_price_ex_vat' => 0,
            'amount' => 0,
            'line_total_ex_vat' => 0,
            'vat_rate' => 0,
            'line_vat_amount' => 0,
            'category' => 'header',
            'is_billable' => false,
            'sort_order' => $sortOrder,
        ]);
        $sortOrder += 10;
    }

    protected function createTaskHeader(Invoice $invoice, $task, &$sortOrder): void
    {
        InvoiceLine::create([
            'invoice_id' => $invoice->id,
            'source_type' => 'task_header',
            'group_task_id' => $task->id,
            'group_milestone_id' => $task->project_milestone_id,
            'description' => '→ ' . $task->name,
            'quantity' => 0,
            'unit' => 'hours',
            'unit_price' => 0,
            'unit_price_ex_vat' => 0,
            'amount' => 0,
            'line_total_ex_vat' => 0,
            'vat_rate' => 0,
            'line_vat_amount' => 0,
            'category' => 'header',
            'is_billable' => false,
            'sort_order' => $sortOrder,
        ]);
        $sortOrder += 10;
    }

    protected function createTaskLines(Invoice $invoice, $taskId, $entries, &$sortOrder): void
    {
        // Group entries by milestone + task + description combination
        $groupedEntries = [];

        foreach ($entries as $entry) {
            $groupKey = $entry->project_milestone_id . '|' . $entry->project_task_id . '|' . trim($entry->description ?: 'Time entry');

            if (!isset($groupedEntries[$groupKey])) {
                $groupedEntries[$groupKey] = [
                    'entries' => [],
                    'milestone_id' => $entry->project_milestone_id,
                    'task_id' => $entry->project_task_id,
                    'description' => $entry->description ?: 'Time entry',
                ];
            }

            $groupedEntries[$groupKey]['entries'][] = $entry;
        }

        // Create invoice lines for each grouped set
        foreach ($groupedEntries as $group) {
            $groupEntries = $group['entries'];
            $description = '• ' . $group['description'];

            // Calculate totals for this group
            $totalTime = 0;
            $totalAmount = 0;
            $timeEntryIds = [];
            $dates = [];
            $users = [];
            $isDeferredItem = false;
            $deferHistory = null;

            foreach ($groupEntries as $entry) {
                $entryTime = $entry->hours + ($entry->minutes / 60);
                $hourlyRate = $entry->hourly_rate_used ?? $this->project->default_hourly_rate ?? 0;

                $totalTime += $entryTime;
                $totalAmount += $entryTime * $hourlyRate;
                $timeEntryIds[] = $entry->id;
                $dates[] = $entry->entry_date->format('Y-m-d');
                $users[] = $entry->user->name ?? 'Unknown';

                // Check if this is a deferred item (via _defer_mapping)
                if (isset($entry->_defer_mapping)) {
                    $isDeferredItem = true;
                    if (isset($entry->_defer_history)) {
                        $deferHistory = $entry->_defer_history;
                    }
                }
            }

            // Calculate average hourly rate for the group
            $averageHourlyRate = $totalTime > 0 ? round($totalAmount / $totalTime, 2) : 0;

            // Build metadata
            $metadata = [
                'time_entry_ids' => $timeEntryIds,
                'dates' => array_unique($dates),
                'users' => array_unique($users),
                'entry_count' => count($groupEntries),
                'consolidated' => count($groupEntries) > 1,
            ];

            // Add defer info if this is a deferred item
            if ($isDeferredItem) {
                $metadata['is_deferred_item'] = true;
                if ($deferHistory) {
                    $metadata['defer_history'] = $deferHistory;
                }
                $metadata['original_project_id'] = $groupEntries[0]->project_id;
            }

            // Create consolidated invoice line
            $line = InvoiceLine::create([
                'invoice_id' => $invoice->id,
                'source_type' => 'time_entry',
                'source_id' => count($groupEntries) === 1 ? $groupEntries[0]->id : null, // Only set if single entry
                'group_milestone_id' => $group['milestone_id'],
                'group_task_id' => $group['task_id'],
                'description' => $description,
                'detailed_description' => count($groupEntries) > 1 ?
                    'Consolidated from ' . count($groupEntries) . ' time entries' :
                    ($groupEntries[0]->notes ?: null),
                'quantity' => round($totalTime, 2),
                'unit' => 'hours',
                'unit_price' => $averageHourlyRate,
                'unit_price_ex_vat' => $averageHourlyRate,
                'amount' => $totalAmount,
                'line_total_ex_vat' => $totalAmount,
                'vat_rate' => $this->project->vat_rate ?? 21,
                'line_vat_amount' => $totalAmount * (($this->project->vat_rate ?? 21) / 100),
                'category' => 'work',
                'is_billable' => true,
                'defer_to_next_month' => false, // Can be changed in invoice edit
                'sort_order' => $sortOrder,
                'metadata' => json_encode($metadata)
            ]);

            // Mark all time entries in this group as invoiced
            foreach ($groupEntries as $entry) {
                // Store previous defer state if entry was deferred
                $updateData = [
                    'invoice_id' => $invoice->id,
                    'invoice_line_id' => $line->id,
                    'is_invoiced' => true,
                    'invoiced_at' => now(),
                    'invoiced_hours' => $entry->hours,
                    'invoiced_rate' => $entry->hourly_rate_used,
                    'invoiced_description' => $entry->description,
                    'invoiced_modified_at' => now(),
                    'invoiced_modified_by' => Auth::id(),
                ];

                // If entry was deferred, update milestone/task to current project and store its state
                if ($entry->was_deferred) {
                    $updateData['was_previously_deferred'] = true;
                    $updateData['previous_deferred_at'] = $entry->deferred_at;
                    $updateData['previous_deferred_by'] = $entry->deferred_by;
                    $updateData['previous_defer_reason'] = $entry->defer_reason;
                    // Reset current deferred status
                    $updateData['was_deferred'] = false;
                    $updateData['deferred_at'] = null;
                    $updateData['deferred_by'] = null;
                    $updateData['defer_reason'] = null;
                    // Update milestone/task to current project (group IDs zijn al gemapped)
                    $updateData['project_milestone_id'] = $group['milestone_id'];
                    $updateData['project_task_id'] = $group['task_id'];
                }

                // KRITIEK: Gebruik DB::table()->update() om ALLEEN $updateData velden te updaten
                // Dit voorkomt dat runtime properties (_defer_mapping, etc) per ongeluk naar DB gaan
                \DB::table('time_entries')
                    ->where('id', $entry->id)
                    ->update(array_merge($updateData, ['updated_at' => now()]));
            }

            $sortOrder += 10;
        }
    }

    protected function createMilestoneTotal(Invoice $invoice, $milestoneId, $entries, &$sortOrder): void
    {
        if (empty($entries)) return;

        // Calculate milestone totals
        $totalHours = 0;
        $totalAmount = 0;

        foreach ($entries as $entry) {
            $totalHours += $entry->hours + ($entry->minutes / 60);
            $hourlyRate = $entry->hourly_rate_used ?? $this->project->default_hourly_rate ?? 0;
            $totalAmount += ($entry->hours + ($entry->minutes / 60)) * $hourlyRate;
        }

        // Get milestone name
        $milestone = $entries[0]->milestone;
        $milestoneName = $milestone ? $milestone->name : 'General Work';

        InvoiceLine::create([
            'invoice_id' => $invoice->id,
            'source_type' => 'milestone_total',
            'group_milestone_id' => $milestoneId,
            'description' => $milestoneName . ' (Milestone Total)',
            'quantity' => round($totalHours, 2),
            'unit' => 'hours',
            'unit_price' => $totalAmount > 0 ? round($totalAmount / $totalHours, 2) : 0,
            'unit_price_ex_vat' => $totalAmount > 0 ? round($totalAmount / $totalHours, 2) : 0,
            'amount' => $totalAmount,
            'line_total_ex_vat' => $totalAmount,
            'vat_rate' => $this->project->vat_rate ?? 21,
            'line_vat_amount' => $totalAmount * (($this->project->vat_rate ?? 21) / 100),
            'category' => 'total',
            'is_billable' => true,
            'sort_order' => $sortOrder,
            'metadata' => json_encode([
                'milestone_id' => $milestoneId,
                'entry_count' => count($entries),
            ])
        ]);
        $sortOrder += 10;
    }
    
    /**
     * Generate service package lines
     */
    protected function generateServicePackageLines(Invoice $invoice): void
    {
        // Get service items from project milestones/tasks/subtasks
        $serviceMilestones = ProjectMilestone::where('project_id', $this->project->id)
            ->where('is_service_item', true)
            ->whereNotNull('original_service_id')
            ->get();
        
        $sortOrder = 1000; // Service items come after work items
        
        foreach ($serviceMilestones as $milestone) {
            // Check if this should be included this month or deferred
            $serviceAmount = $milestone->fixed_price ?? 0;
            $line = InvoiceLine::create([
                'invoice_id' => $invoice->id,
                'source_type' => 'service',
                'source_id' => $milestone->id,
                'group_milestone_id' => $milestone->id,
                'description' => $milestone->service_name ?? $milestone->name,
                'detailed_description' => $milestone->description,
                'quantity' => 1,
                'unit' => 'package',
                'unit_price' => $serviceAmount,
                'unit_price_ex_vat' => $serviceAmount,
                'amount' => $serviceAmount,
                'line_total_ex_vat' => $serviceAmount,
                'vat_rate' => $this->project->vat_rate ?? 21,
                'line_vat_amount' => $serviceAmount * (($this->project->vat_rate ?? 21) / 100),
                'category' => 'service',
                'is_billable' => true,
                'is_service_package' => true,
                'service_id' => $milestone->original_service_id,
                'service_color' => $milestone->service_color,
                'sort_order' => $sortOrder,
                'defer_to_next_month' => false, // Can be edited in draft
            ]);
            
            $sortOrder += 10;
        }
    }
    
    /**
     * Generate additional cost lines
     */
    protected function generateAdditionalCostLines(Invoice $invoice): void
    {
        // One-time costs for this period
        $oneTimeCosts = ProjectAdditionalCost::where('project_id', $this->project->id)
            ->where('cost_type', 'one_time')
            ->where('is_active', true)
            ->whereBetween('start_date', [$this->periodStart, $this->periodEnd])
            ->get();
        
        // Recurring costs active in this period
        $recurringCosts = ProjectAdditionalCost::where('project_id', $this->project->id)
            ->where('cost_type', 'monthly_recurring')
            ->where('is_active', true)
            ->where('start_date', '<=', $this->periodEnd)
            ->where(function($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', $this->periodStart);
            })
            ->get();
        
        $sortOrder = 2000; // Additional costs come last
        
        foreach ($oneTimeCosts as $cost) {
            InvoiceLine::create([
                'invoice_id' => $invoice->id,
                'source_type' => 'additional_cost',
                'source_id' => $cost->id,
                'description' => $cost->name,
                'detailed_description' => $cost->description,
                'quantity' => 1,
                'unit' => 'piece',
                'unit_price' => $cost->amount,
                'unit_price_ex_vat' => $cost->amount,
                'amount' => $cost->amount,
                'line_total_ex_vat' => $cost->amount,
                'vat_rate' => $this->project->vat_rate ?? 21,
                'line_vat_amount' => $cost->amount * (($this->project->vat_rate ?? 21) / 100),
                'category' => 'cost',
                'is_billable' => $cost->fee_type === 'additional',
                'sort_order' => $sortOrder,
            ]);
            $sortOrder += 10;
        }
        
        foreach ($recurringCosts as $cost) {
            InvoiceLine::create([
                'invoice_id' => $invoice->id,
                'source_type' => 'additional_cost',
                'source_id' => $cost->id,
                'description' => $cost->name . ' (Monthly)',
                'detailed_description' => $cost->description,
                'quantity' => 1,
                'unit' => 'month',
                'unit_price' => $cost->amount,
                'unit_price_ex_vat' => $cost->amount,
                'amount' => $cost->amount,
                'line_total_ex_vat' => $cost->amount,
                'vat_rate' => $this->project->vat_rate ?? 21,
                'line_vat_amount' => $cost->amount * (($this->project->vat_rate ?? 21) / 100),
                'category' => 'cost',
                'is_billable' => $cost->fee_type === 'additional',
                'sort_order' => $sortOrder,
            ]);
            $sortOrder += 10;
        }
    }
    
    /**
     * Include items that were deferred from previous invoices
     */
    protected function includeDeferredItems(Invoice $invoice): void
    {
        // Only for recurring projects
        if (!in_array($this->project->billing_frequency, ['monthly', 'quarterly'])) {
            return;
        }

        // KRITIEK: Voor recurring series zoeken we deferred items uit ALLE projecten in de serie!
        // Dit werkt voor aparte maandprojecten die via recurring_series_id aan elkaar gelinkt zijn
        $query = TimeEntry::where('time_entries.status', 'approved')
            ->where('time_entries.was_deferred', true)
            ->whereNull('time_entries.invoice_id');

        $seriesProjectIds = null;
        if ($this->project->recurring_series_id) {
            // Zoek in ALLE projecten met dezelfde recurring_series_id
            $seriesProjectIds = \App\Models\Project::where('recurring_series_id', $this->project->recurring_series_id)
                ->pluck('id')
                ->toArray();
            $query->whereIn('time_entries.project_id', $seriesProjectIds);
        } else {
            // Fallback: alleen dit project (voor oude projecten zonder series)
            $query->where('time_entries.project_id', $this->project->id);
        }

        // Sorteer deferred items ook op milestone/task sort_order voor consistente volgorde
        $deferredTimeEntries = $query
            ->leftJoin('project_milestones', 'time_entries.project_milestone_id', '=', 'project_milestones.id')
            ->leftJoin('project_tasks', 'time_entries.project_task_id', '=', 'project_tasks.id')
            ->orderByRaw('COALESCE(project_milestones.sort_order, 999999) ASC')
            ->orderByRaw('COALESCE(project_tasks.sort_order, 999999) ASC')
            ->orderBy('time_entries.entry_date')
            ->select('time_entries.*')
            ->with(['milestone', 'task', 'user'])
            ->get();

        if ($deferredTimeEntries->count() === 0) {
            Log::info('No deferred items found for recurring series', [
                'project_id' => $this->project->id,
                'recurring_series_id' => $this->project->recurring_series_id,
                'series_project_ids' => $seriesProjectIds,
            ]);
            return;
        }

        Log::info('Found deferred items for recurring series', [
            'project_id' => $this->project->id,
            'recurring_series_id' => $this->project->recurring_series_id,
            'deferred_count' => $deferredTimeEntries->count(),
            'entry_ids' => $deferredTimeEntries->pluck('id')->toArray(),
            'series_project_ids' => $seriesProjectIds,
        ]);

        $sortOrder = 3000; // Deferred items come after additional costs

        // KRITIEK: Deferred items worden NIET gegroepeerd - elke entry blijft apart
        // Dit zorgt ervoor dat aparte taken apart blijven, zelfs met dezelfde beschrijving

        // Voor elk deferred item: probeer de milestone/task te mappen naar het huidige project
        foreach ($deferredTimeEntries as $entry) {
            // Map milestone/task naar huidig project (als ze dezelfde naam hebben)
            $currentMilestoneId = $this->findMatchingMilestone($entry->project_milestone_id);
            $currentTaskId = $this->findMatchingTask($entry->project_task_id, $currentMilestoneId);

            $description = '• ' . ($entry->description ?: 'Time entry');

            // Calculate voor deze enkele entry
            $entryTime = $entry->hours + ($entry->minutes / 60);
            $hourlyRate = $entry->hourly_rate_used ?? $this->project->default_hourly_rate ?? 0;
            $totalAmount = $entryTime * $hourlyRate;

            // Parse defer info from defer_reason
            $deferHistory = [];
            if ($entry->defer_reason) {
                preg_match('/invoice ([A-Z0-9-]+).*\(([^)]+)\)/', $entry->defer_reason, $matches);
                $deferHistory = [
                    'from_invoice_number' => $matches[1] ?? 'unknown',
                    'from_period' => $matches[2] ?? 'previous month',
                ];
            }

            // Build metadata voor deze enkele entry
            $metadata = [
                'time_entry_ids' => [$entry->id],
                'date' => $entry->entry_date->format('Y-m-d'),
                'user' => $entry->user->name ?? 'Unknown',
                'is_deferred_item' => true,
                'defer_history' => $deferHistory,
                'original_project_id' => $entry->project_id,
            ];

            // Create invoice line - description blijft ongewijzigd
            // Defer info staat in metadata en wordt getoond in de oranje badge
            $line = InvoiceLine::create([
                'invoice_id' => $invoice->id,
                'source_type' => 'time_entry',
                'source_id' => $entry->id,
                'group_milestone_id' => $currentMilestoneId, // Gebruik gemapte milestone
                'group_task_id' => $currentTaskId, // Gebruik gemapte task
                'description' => $description, // Originele beschrijving zonder [DEFERRED] tekst
                'detailed_description' => 'Originally from invoice ' .
                    ($deferHistory['from_invoice_number'] ?? 'unknown') .
                    '. Entry date: ' . $entry->entry_date->format('d-m-Y') .
                    '. User: ' . ($entry->user->name ?? 'Unknown'),
                'quantity' => round($entryTime, 2),
                'unit' => 'hours',
                'unit_price' => $hourlyRate,
                'unit_price_ex_vat' => $hourlyRate,
                'amount' => $totalAmount,
                'line_total_ex_vat' => $totalAmount,
                'vat_rate' => $this->project->vat_rate ?? 21,
                'line_vat_amount' => $totalAmount * (($this->project->vat_rate ?? 21) / 100),
                'category' => 'work',
                'is_billable' => true,
                'defer_to_next_month' => false,
                'sort_order' => $sortOrder,
                'metadata' => json_encode($metadata)
            ]);

            // Mark deze entry als invoiced in nieuwe invoice
            $entry->update([
                'invoice_id' => $invoice->id,
                'invoice_line_id' => $line->id,
                'is_invoiced' => true,
                'invoiced_at' => now(),
                'was_deferred' => false, // Reset defer flag after including
                'project_milestone_id' => $currentMilestoneId, // Update naar nieuwe milestone
                'project_task_id' => $currentTaskId, // Update naar nieuwe task
            ]);

            $sortOrder += 10;
        }

        Log::info('Included deferred items in new invoice', [
            'invoice_id' => $invoice->id,
            'project_id' => $this->project->id,
            'deferred_entries_count' => $deferredTimeEntries->count(),
        ]);
    }

    /**
     * Zoek matching milestone in huidig project op basis van naam
     */
    protected function findMatchingMilestone($originalMilestoneId): ?int
    {
        if (!$originalMilestoneId) {
            return null;
        }

        // Haal originele milestone op
        $originalMilestone = ProjectMilestone::find($originalMilestoneId);
        if (!$originalMilestone) {
            return null;
        }

        // Zoek milestone met dezelfde naam in huidig project
        $matchingMilestone = ProjectMilestone::where('project_id', $this->project->id)
            ->where('name', $originalMilestone->name)
            ->first();

        return $matchingMilestone ? $matchingMilestone->id : null;
    }

    /**
     * Zoek matching task in huidig project op basis van naam
     */
    protected function findMatchingTask($originalTaskId, $currentMilestoneId): ?int
    {
        if (!$originalTaskId || !$currentMilestoneId) {
            return null;
        }

        // Haal originele task op
        $originalTask = ProjectTask::find($originalTaskId);
        if (!$originalTask) {
            return null;
        }

        // Zoek task met dezelfde naam binnen de gematchte milestone
        $matchingTask = ProjectTask::where('project_milestone_id', $currentMilestoneId)
            ->where('name', $originalTask->name)
            ->first();

        return $matchingTask ? $matchingTask->id : null;
    }

    /**
     * Calculate invoice totals and rollover
     */
    protected function calculateInvoiceTotals(Invoice $invoice): void
    {
        // KRITIEK: Filter deferred items uit - die tellen NIET mee deze maand!
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

        // Calculate subtotal (only billable items - NIET in_fee costs)
        $subtotal = $lines->where('is_billable', true)
            ->where('defer_to_next_month', false)
            ->sum('line_total_ex_vat');

        // Calculate VAT
        $vatAmount = $subtotal * ($invoice->vat_rate / 100);
        $totalAmount = $subtotal + $vatAmount;

        // Calculate rollover to next month - gebaseerd op work + service (inclusief in_fee costs)
        $totalCosts = $workAmount + $serviceAmount;
        $nextMonthRollover = 0;

        // KRITIEK: Gebruik monthly_budget (NIET total_budget) voor nieuwe rollover berekening
        // Anders wordt de oude rollover dubbel geteld!
        if ($this->project->fee_rollover_enabled && $this->monthlyBudget > 0) {
            if ($totalCosts < $this->monthlyBudget) {
                // Positive rollover (budget remaining THIS month)
                $nextMonthRollover = $this->monthlyBudget - $totalCosts;
            } else {
                // Negative rollover (budget exceeded THIS month)
                $nextMonthRollover = -($totalCosts - $this->monthlyBudget);
            }
        }

        // Update invoice
        $invoice->update([
            'work_amount' => $workAmount, // Inclusief in_fee costs
            'service_amount' => $serviceAmount,
            'additional_costs' => $additionalCosts, // ALLEEN billable additional costs
            'subtotal' => $subtotal, // ALLEEN billable items
            'vat_amount' => $vatAmount,
            'total_amount' => $totalAmount,
            'next_month_rollover' => $nextMonthRollover,
        ]);
    }
    
    /**
     * Build work item description from IDs
     */
    protected function buildWorkItemDescription($milestoneId, $taskId, $subtaskId): string
    {
        $parts = [];

        // Subtasks disabled - start with task level
        if ($taskId > 0) {
            $task = ProjectTask::find($taskId);
            if ($task) {
                $milestone = $task->milestone;
                $parts[] = $milestone->name;
                $parts[] = $task->name;
            }
        } elseif ($milestoneId > 0) {
            $milestone = ProjectMilestone::find($milestoneId);
            if ($milestone) {
                $parts[] = $milestone->name;
            }
        } else {
            $parts[] = 'General Work';
        }
        
        return implode(' → ', $parts);
    }
    
    /**
     * Build detailed description from time entries
     */
    protected function buildDetailedDescription($entries): string
    {
        $details = [];
        foreach ($entries->take(5) as $entry) {
            $details[] = sprintf(
                "%s: %s (%s hours)",
                $entry->entry_date->format('d/m'),
                $entry->user->name,
                number_format($entry->hours, 2)
            );
        }
        
        if ($entries->count() > 5) {
            $details[] = sprintf("... and %d more entries", $entries->count() - 5);
        }
        
        return implode("\n", $details);
    }
    
    /**
     * Generate invoice number
     */
    protected function generateInvoiceNumber(): string
    {
        $year = now()->year;
        $lastInvoice = Invoice::where('invoice_number', 'like', "INV-{$year}-%")
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
     * Generate quarterly invoice
     */
    protected function generateQuarterlyInvoice(): Invoice
    {
        // Similar to monthly but for 3 months
        return $this->generateMonthlyInvoice();
    }
    
    /**
     * Generate milestone-based invoice
     */
    protected function generateMilestoneInvoice(): Invoice
    {
        // Invoice when milestones are completed
        return $this->generateMonthlyInvoice();
    }
    
    /**
     * Generate project completion invoice
     */
    protected function generateProjectCompletionInvoice(): Invoice
    {
        // Invoice everything at project completion
        return $this->generateMonthlyInvoice();
    }
    
    /**
     * Generate custom/manual invoice
     */
    protected function generateCustomInvoice(): Invoice
    {
        // Manual invoice creation
        return $this->generateMonthlyInvoice();
    }
    
    /**
     * Determine which invoice template to use
     * Priority: Project -> Customer -> Company default -> System default
     */
    protected function determineInvoiceTemplate(): ?int
    {
        // 1. Check if project has a specific template
        if ($this->project->invoice_template_id) {
            return $this->project->invoice_template_id;
        }
        
        // 2. Check if customer has a preferred template
        $customer = $this->project->customer;
        if ($customer && $customer->invoice_template_id) {
            return $customer->invoice_template_id;
        }
        
        // 3. Check for company default template
        $companyId = $this->project->company_id;
        $companyDefault = \App\Models\InvoiceTemplate::where('company_id', $companyId)
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();
        
        if ($companyDefault) {
            return $companyDefault->id;
        }
        
        // 4. Use system default template
        $systemDefault = \App\Models\InvoiceTemplate::whereNull('company_id')
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();
        
        if ($systemDefault) {
            return $systemDefault->id;
        }
        
        // 5. Fallback to first available template
        $firstTemplate = \App\Models\InvoiceTemplate::where('is_active', true)->first();
        
        return $firstTemplate ? $firstTemplate->id : null;
    }
}