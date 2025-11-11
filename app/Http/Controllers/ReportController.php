<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Project;
use App\Services\InvoiceGenerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Display a listing of all reports (invoices)
     */
    public function index(Request $request)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators and project managers can view reports.');
        }

        // Query building met filters
        $query = Invoice::with(['project.customer', 'project.companyRelation']);

        // Company isolation - alleen voor project_manager
        if (Auth::user()->role === 'project_manager') {
            $query->whereHas('project', function($q) {
                $q->where('company_id', Auth::user()->company_id);
            });
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', '%' . $search . '%')
                  ->orWhereHas('project', function($pq) use ($search) {
                      $pq->where('name', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('project.customer', function($cq) use ($search) {
                      $cq->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Customer filter
        if ($request->filled('customer_id')) {
            $query->whereHas('project', function($q) use ($request) {
                $q->where('customer_id', $request->customer_id);
            });
        }

        // Sorteer op nieuwste eerst
        $reports = $query->orderBy('created_at', 'desc')->paginate(20);

        // Statistics
        $stats = [
            'total' => Invoice::when(Auth::user()->role === 'project_manager', function($q) {
                $q->whereHas('project', function($pq) {
                    $pq->where('company_id', Auth::user()->company_id);
                });
            })->count(),
            'draft' => Invoice::where('status', 'draft')
                ->when(Auth::user()->role === 'project_manager', function($q) {
                    $q->whereHas('project', function($pq) {
                        $pq->where('company_id', Auth::user()->company_id);
                    });
                })->count(),
            'finalized' => Invoice::where('status', 'finalized')
                ->when(Auth::user()->role === 'project_manager', function($q) {
                    $q->whereHas('project', function($pq) {
                        $pq->where('company_id', Auth::user()->company_id);
                    });
                })->count(),
        ];

        // Haal alle customers voor filter dropdown
        $customers = Customer::when(Auth::user()->role !== 'super_admin', function($q) {
            $q->where('company_id', Auth::user()->company_id);
        })->orderBy('name')->get();

        return view('reports.index', compact('reports', 'stats', 'customers'));
    }

    /**
     * Show the form for creating a new report
     */
    public function create()
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied.');
        }

        // Haal customers op basis van role
        $customers = Customer::when(Auth::user()->role !== 'super_admin', function($q) {
            $q->where('company_id', Auth::user()->company_id);
        })->orderBy('name')->get();

        return view('reports.create', compact('customers'));
    }

    /**
     * Store a newly created report (redirect to spreadsheet)
     */
    public function store(Request $request)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied.');
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'project_id' => 'required|exists:projects,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
        ]);

        try {
            // Check of er al een draft invoice bestaat voor dit project EN deze periode
            $periodStart = Carbon::parse($validated['period_start']);
            $periodEnd = Carbon::parse($validated['period_end']);

            $existingInvoice = Invoice::where('project_id', $validated['project_id'])
                ->where('status', 'draft')
                ->where('period_start', $periodStart->format('Y-m-d'))
                ->where('period_end', $periodEnd->format('Y-m-d'))
                ->latest()
                ->first();

            if ($existingInvoice) {
                // Redirect naar bestaande draft
                return redirect()->route('reports.spreadsheet', $existingInvoice)
                    ->with('info', 'Opening existing draft report for this period.');
            }

            // Haal project op
            $project = Project::with(['customer', 'companyRelation'])->findOrFail($validated['project_id']);

            // Use InvoiceGenerationService to create invoice with all time entries
            $invoiceService = new InvoiceGenerationService();

            $invoice = $invoiceService->generateForProject($project, $periodStart, $periodEnd);

            Log::info('Report created via InvoiceGenerationService', [
                'invoice_id' => $invoice->id,
                'project_id' => $project->id,
                'period_start' => $periodStart->format('Y-m-d'),
                'period_end' => $periodEnd->format('Y-m-d'),
            ]);

            // Redirect naar spreadsheet view
            return redirect()->route('reports.spreadsheet', $invoice)
                ->with('success', 'New report created successfully with all time entries!');

        } catch (\Exception $e) {
            Log::error('Error creating report', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->withInput()
                ->with('error', 'Error creating report: ' . $e->getMessage());
        }
    }

    /**
     * Show the report spreadsheet view
     */
    public function spreadsheet(Invoice $invoice)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied.');
        }

        // Company isolation check
        if (Auth::user()->role !== 'super_admin' && $invoice->invoicing_company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied.');
        }

        // Gebruik dezelfde logic als InvoiceController maar vanuit reports context
        return app(\App\Http\Controllers\InvoiceController::class)->spreadsheet($invoice);
    }

    /**
     * Show the finalize view for a report
     */
    public function finalizeView(Invoice $invoice)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied.');
        }

        // Company isolation check
        if (Auth::user()->role !== 'super_admin' && $invoice->invoicing_company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied.');
        }

        // Gebruik dezelfde logic als InvoiceController maar vanuit reports context
        return app(\App\Http\Controllers\InvoiceController::class)->finalizeView($invoice);
    }

    /**
     * Confirm and finalize a report
     */
    public function finalizeConfirm(Invoice $invoice)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied.');
        }

        // Company isolation check
        if (Auth::user()->role !== 'super_admin' && $invoice->invoicing_company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied.');
        }

        // Check if already finalized
        if ($invoice->status === 'finalized') {
            return redirect()->route('reports.show', $invoice)
                ->with('error', 'Report is already finalized.');
        }

        // Gebruik InvoiceController logic maar redirect naar reports
        $result = app(\App\Http\Controllers\InvoiceController::class)->finalizeConfirm($invoice);

        // Als het een redirect is naar invoices.show, verander naar reports.show
        if ($result instanceof \Illuminate\Http\RedirectResponse) {
            $url = $result->getTargetUrl();
            if (str_contains($url, '/invoices/')) {
                $url = str_replace('/invoices/', '/reports/', $url);
                return redirect($url)->with($result->getSession()->get('success') ? 'success' : 'error',
                    $result->getSession()->get('success') ?: $result->getSession()->get('error'));
            }
        }

        return redirect()->route('reports.show', $invoice)
            ->with('success', 'Report has been finalized successfully.');
    }

    /**
     * Show a finalized report
     */
    public function show(Invoice $invoice)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied.');
        }

        // Company isolation check
        if (Auth::user()->role !== 'super_admin' && $invoice->invoicing_company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied.');
        }

        // Gebruik dezelfde logic als InvoiceController maar vanuit reports context
        return app(\App\Http\Controllers\InvoiceController::class)->show($invoice);
    }

    /**
     * Delete a draft report
     */
    public function destroy(Invoice $invoice)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can delete reports.');
        }

        // Only allow deletion of draft reports
        if ($invoice->status !== 'draft') {
            return back()->with('error', 'Only draft reports can be deleted. Finalized reports cannot be removed.');
        }

        // Check company access
        if (Auth::user()->role !== 'super_admin' && $invoice->invoicing_company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied.');
        }

        try {
            DB::beginTransaction();

            // Reset time entries
            $timeEntries = \App\Models\TimeEntry::where('invoice_id', $invoice->id)->get();

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
                    $updateData['was_previously_deferred'] = false;
                    $updateData['previous_deferred_at'] = null;
                    $updateData['previous_deferred_by'] = null;
                    $updateData['previous_defer_reason'] = null;
                }

                $entry->update($updateData);
            }

            // Delete invoice lines
            \App\Models\InvoiceLine::where('invoice_id', $invoice->id)->delete();

            // Delete the invoice
            $invoice->delete();

            DB::commit();

            Log::info('Draft report deleted', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'deleted_by' => Auth::id(),
            ]);

            return redirect()->route('reports.index')
                ->with('success', 'Draft report deleted successfully. All time entries have been reset.');

        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Error deleting draft report', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Error deleting report: ' . $e->getMessage());
        }
    }

    /**
     * Get projects for a customer (AJAX endpoint)
     */
    public function getProjectsForCustomer(Request $request, $customerId)
    {
        // Authorization check
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $customer = Customer::findOrFail($customerId);

        // Company isolation check
        if (Auth::user()->role === 'project_manager') {
            if ($customer->company_id !== Auth::user()->company_id) {
                return response()->json(['error' => 'Access denied'], 403);
            }
        }

        $projects = Project::where('customer_id', $customerId)
            ->where('status', '!=', 'cancelled')
            ->orderBy('name')
            ->get(['id', 'name', 'status', 'start_date', 'end_date']);

        return response()->json($projects);
    }
}
