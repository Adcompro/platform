<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Project;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Company;
use App\Models\TimeEntry;
use App\Models\ProjectAdditionalCost;

class InvoiceDashboardController extends Controller
{
    /**
     * Display the invoice generation dashboard
     * Toont welke bedrijven facturen moeten krijgen
     */
    public function index(Request $request)
    {
        // Check authorization - alleen admin en super_admin
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can view the invoice dashboard.');
        }

        $user = Auth::user();
        
        // Get filter parameters
        $selectedMonth = $request->get('month', Carbon::now()->format('Y-m'));
        $selectedCompany = $request->get('company_id');
        
        // Parse month for date range
        $startDate = Carbon::parse($selectedMonth . '-01')->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        
        // Build query voor projecten die gefactureerd moeten worden
        $query = Project::with([
            'customer',
            'mainInvoicingCompany',
            'companies',
            'timeEntries' => function($q) use ($startDate, $endDate) {
                $q->whereBetween('entry_date', [$startDate, $endDate])
                  ->where('status', 'approved')
                  ->where('is_invoiced', false);
            },
            'additionalCosts' => function($q) use ($startDate, $endDate) {
                $q->where(function($query) use ($startDate, $endDate) {
                    // One-time costs in this period
                    $query->where('cost_type', 'one_time')
                          ->whereBetween('start_date', [$startDate, $endDate]);
                })->orWhere(function($query) use ($startDate, $endDate) {
                    // Recurring costs active in this period
                    $query->where('cost_type', 'monthly_recurring')
                          ->where('start_date', '<=', $endDate)
                          ->where(function($q) use ($startDate) {
                              $q->whereNull('end_date')
                                ->orWhere('end_date', '>=', $startDate);
                          });
                });
            },
            'invoices' => function($q) use ($startDate, $endDate) {
                $q->where('period_start', '>=', $startDate)
                  ->where('period_end', '<=', $endDate);
            }
        ])
        ->where('status', 'active');
        
        // Company filtering voor non-super_admin
        if ($user->role !== 'super_admin') {
            $query->whereHas('companies', function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            });
        } elseif ($selectedCompany) {
            $query->whereHas('companies', function($q) use ($selectedCompany) {
                $q->where('company_id', $selectedCompany);
            });
        }
        
        // Filter alleen projecten met billing frequency die actief is
        $query->where(function($q) use ($startDate, $endDate) {
            $q->where('billing_frequency', 'monthly')
              ->orWhere(function($subQ) use ($startDate, $endDate) {
                  $subQ->where('billing_frequency', 'quarterly')
                       ->whereRaw('MONTH(next_billing_date) = ?', [$startDate->month]);
              })
              ->orWhere(function($subQ) {
                  $subQ->where('billing_frequency', 'milestone')
                       ->whereHas('milestones', function($milestoneQ) {
                          $milestoneQ->where('status', 'completed')
                                     ->where('invoicing_trigger', 'on_completion');
                       });
              });
        });
        
        $projects = $query->get();
        
        // Groepeer projecten per invoicing company
        $invoiceData = [];
        
        foreach ($projects as $project) {
            $companyId = $project->main_invoicing_company_id ?? $project->company_id;
            $company = Company::find($companyId);
            
            if (!$company) continue;
            
            if (!isset($invoiceData[$companyId])) {
                $invoiceData[$companyId] = [
                    'company' => $company,
                    'customers' => [],
                    'total_amount' => 0,
                    'total_hours' => 0,
                    'project_count' => 0
                ];
            }
            
            $customerId = $project->customer_id;
            if (!isset($invoiceData[$companyId]['customers'][$customerId])) {
                $invoiceData[$companyId]['customers'][$customerId] = [
                    'customer' => $project->customer,
                    'projects' => [],
                    'subtotal_amount' => 0,
                    'subtotal_hours' => 0
                ];
            }
            
            // Bereken bedragen voor dit project
            $projectData = $this->calculateProjectInvoiceData($project, $startDate, $endDate);
            
            // Check of er al een factuur is voor deze periode
            $existingInvoice = $project->invoices
                ->where('period_start', '>=', $startDate)
                ->where('period_end', '<=', $endDate)
                ->first();
            
            $projectData['existing_invoice'] = $existingInvoice;
            $projectData['project'] = $project;
            
            // Check voor gemiste facturen en bereken bedragen
            $missedPeriods = $project->getMissedInvoicePeriods();
            
            // Bereken bedrag voor elke gemiste periode
            foreach ($missedPeriods as &$period) {
                $periodData = $this->calculateProjectInvoiceData(
                    $project, 
                    $period['period_start'], 
                    $period['period_end']
                );
                $period['amount'] = $periodData['total_amount'];
                $period['hours'] = $periodData['total_hours'];
            }
            
            $projectData['missed_periods'] = $missedPeriods;
            $projectData['has_missed_invoices'] = count($missedPeriods) > 0;
            $projectData['missed_total_amount'] = array_sum(array_column($missedPeriods, 'amount'));
            
            // Add to grouped data
            $invoiceData[$companyId]['customers'][$customerId]['projects'][] = $projectData;
            $invoiceData[$companyId]['customers'][$customerId]['subtotal_amount'] += $projectData['total_amount'];
            $invoiceData[$companyId]['customers'][$customerId]['subtotal_hours'] += $projectData['total_hours'];
            
            $invoiceData[$companyId]['total_amount'] += $projectData['total_amount'];
            $invoiceData[$companyId]['total_hours'] += $projectData['total_hours'];
            $invoiceData[$companyId]['project_count']++;
        }
        
        // Get companies for filter dropdown
        if ($user->role === 'super_admin') {
            $companies = Company::where('is_active', true)->orderBy('name')->get();
        } else {
            $companies = Company::where('id', $user->company_id)->get();
        }
        
        // Generate month options (last 6 months + next 3 months)
        $monthOptions = [];
        for ($i = -6; $i <= 3; $i++) {
            $date = Carbon::now()->addMonths($i);
            $monthOptions[$date->format('Y-m')] = $date->format('F Y');
        }
        
        return view('invoices.dashboard', compact(
            'invoiceData',
            'companies',
            'monthOptions',
            'selectedMonth',
            'selectedCompany',
            'startDate',
            'endDate'
        ));
    }
    
    /**
     * Bereken invoice data voor een project
     */
    private function calculateProjectInvoiceData($project, $startDate, $endDate)
    {
        $data = [
            'total_hours' => 0,
            'total_amount' => 0,
            'time_entry_amount' => 0,
            'additional_costs_amount' => 0,
            'monthly_fee' => $project->monthly_fee ?? 0,
            'has_uninvoiced_entries' => false,
            'ready_for_invoice' => false
        ];
        
        // Bereken time entry kosten
        $timeEntries = $project->timeEntries()
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->where('status', 'approved')
            ->where('is_invoiced', false)
            ->get();
        
        foreach ($timeEntries as $entry) {
            $hours = $entry->hours + ($entry->minutes / 60);
            $data['total_hours'] += $hours;
            
            // Get hourly rate (gebruik hierarchy)
            $hourlyRate = $this->getHourlyRate($entry, $project);
            $data['time_entry_amount'] += $hours * $hourlyRate;
        }
        
        if ($timeEntries->count() > 0) {
            $data['has_uninvoiced_entries'] = true;
        }
        
        // Bereken additional costs
        $additionalCosts = $project->additionalCosts()
            ->where('is_active', true)
            ->where('fee_type', 'additional') // Alleen additional costs, niet in_fee
            ->where(function($query) use ($startDate, $endDate) {
                // One-time costs in this period
                $query->where(function($q) use ($startDate, $endDate) {
                    $q->where('cost_type', 'one_time')
                      ->whereBetween('start_date', [$startDate, $endDate]);
                })->orWhere(function($q) use ($startDate, $endDate) {
                    // Recurring costs active in this period
                    $q->where('cost_type', 'monthly_recurring')
                      ->where('start_date', '<=', $endDate)
                      ->where(function($subQ) use ($startDate) {
                          $subQ->whereNull('end_date')
                               ->orWhere('end_date', '>=', $startDate);
                      });
                });
            })
            ->get();
        
        foreach ($additionalCosts as $cost) {
            $data['additional_costs_amount'] += $cost->amount;
        }
        
        // Bereken totaal
        $data['total_amount'] = $data['time_entry_amount'] + $data['additional_costs_amount'] + $data['monthly_fee'];
        
        // Check of project klaar is voor facturatie
        $data['ready_for_invoice'] = $this->isReadyForInvoicing($project, $startDate, $endDate, $data);
        
        return $data;
    }
    
    /**
     * Get hourly rate voor time entry met hierarchy
     */
    private function getHourlyRate($timeEntry, $project)
    {
        // Check subtask level
        if ($timeEntry->project_subtask_id && $timeEntry->projectSubtask) {
            if ($timeEntry->projectSubtask->pricing_type === 'hourly_rate' && $timeEntry->projectSubtask->hourly_rate_override) {
                return $timeEntry->projectSubtask->hourly_rate_override;
            }
        }
        
        // Check task level
        if ($timeEntry->project_task_id && $timeEntry->projectTask) {
            if ($timeEntry->projectTask->pricing_type === 'hourly_rate' && $timeEntry->projectTask->hourly_rate_override) {
                return $timeEntry->projectTask->hourly_rate_override;
            }
        }
        
        // Check milestone level
        if ($timeEntry->project_milestone_id && $timeEntry->projectMilestone) {
            if ($timeEntry->projectMilestone->pricing_type === 'hourly_rate' && $timeEntry->projectMilestone->hourly_rate_override) {
                return $timeEntry->projectMilestone->hourly_rate_override;
            }
        }
        
        // Check project level
        if ($project->default_hourly_rate) {
            return $project->default_hourly_rate;
        }
        
        // Check company level
        if ($project->mainInvoicingCompany && $project->mainInvoicingCompany->default_hourly_rate) {
            return $project->mainInvoicingCompany->default_hourly_rate;
        }
        
        // Default fallback
        return 75; // Default rate if nothing else is set
    }
    
    /**
     * Check of project klaar is voor facturatie
     */
    private function isReadyForInvoicing($project, $startDate, $endDate, $data)
    {
        // Als er geen bedrag is, niet factureren
        if ($data['total_amount'] <= 0) {
            return false;
        }
        
        // Check billing frequency
        switch ($project->billing_frequency) {
            case 'monthly':
                // Maandelijkse facturatie is altijd klaar aan einde van maand
                return Carbon::now() >= $endDate;
                
            case 'quarterly':
                // Check of we in een kwartaal maand zitten
                return in_array($startDate->month, [3, 6, 9, 12]) && Carbon::now() >= $endDate;
                
            case 'milestone':
                // Check of er completed milestones zijn
                return $project->milestones()
                    ->where('status', 'completed')
                    ->where('invoicing_trigger', 'on_completion')
                    ->whereDoesntHave('invoices')
                    ->exists();
                
            case 'project_completion':
                // Alleen als project completed is
                return $project->status === 'completed';
                
            case 'custom':
                // Check next billing date
                if ($project->next_billing_date) {
                    return Carbon::now() >= Carbon::parse($project->next_billing_date);
                }
                return false;
                
            default:
                return false;
        }
    }
    
    /**
     * Generate invoices voor geselecteerde projecten
     */
    public function generateInvoices(Request $request)
    {
        // Check authorization
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can generate invoices.');
        }
        
        $validated = $request->validate([
            'project_ids' => 'required|array',
            'project_ids.*' => 'exists:projects,id',
            'month' => 'required|date_format:Y-m'
        ]);
        
        $month = $validated['month'];
        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        
        $generatedInvoices = [];
        $errors = [];
        
        DB::beginTransaction();
        
        try {
            foreach ($validated['project_ids'] as $projectId) {
                $project = Project::with(['customer', 'mainInvoicingCompany'])->find($projectId);
                
                // Check of er al een invoice bestaat voor deze periode
                $existingInvoice = Invoice::where('project_id', $projectId)
                    ->where('period_start', $startDate)
                    ->where('period_end', $endDate)
                    ->first();
                
                if ($existingInvoice) {
                    $errors[] = "Invoice already exists for project {$project->name} in this period";
                    continue;
                }
                
                // Generate invoice (deze functionaliteit moet nog verder uitgewerkt worden)
                // Voor nu maken we een draft invoice aan
                $invoice = $this->createDraftInvoice($project, $startDate, $endDate);
                
                if ($invoice) {
                    $generatedInvoices[] = $invoice;
                }
            }
            
            DB::commit();
            
            if (count($generatedInvoices) > 0) {
                return redirect()->route('invoices.dashboard')
                    ->with('success', count($generatedInvoices) . ' invoice(s) generated successfully');
            } else {
                return redirect()->route('invoices.dashboard')
                    ->with('error', 'No invoices could be generated. ' . implode(', ', $errors));
            }
            
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('invoices.dashboard')
                ->with('error', 'Error generating invoices: ' . $e->getMessage());
        }
    }
    
    /**
     * Create draft invoice voor project
     */
    private function createDraftInvoice($project, $startDate, $endDate)
    {
        // Dit wordt later uitgewerkt met InvoiceGenerationService
        // Voor nu placeholder
        return null;
    }
}