<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerActivity;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        // Authorization check - alleen admin en super_admin kunnen alle customers zien
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Insufficient permissions to view customers.');
        }

        // Query building met company filtering
        $query = Customer::query();
        
        // Super admin kan alle companies zien, anderen alleen eigen company
        if (Auth::user()->role !== 'super_admin') {
            $query->where('company_id', Auth::user()->company_id);
        }

        // Search functionaliteit
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('company', 'like', '%' . $request->search . '%')
                  ->orWhere('contact_person', 'like', '%' . $request->search . '%');
            });
        }

        // Status filtering
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Company filtering (voor super_admin)
        if ($request->filled('company_id') && Auth::user()->role === 'super_admin') {
            $query->where('company_id', $request->company_id);
        }

        // Eager loading en ordering
        $customers = $query->with(['companyRelation', 'projects'])
            ->withCount(['projects'])
            ->orderBy('name')
            ->paginate(20);

        // Statistics berekenen
        $stats = [
            'total_customers' => Customer::when(Auth::user()->role !== 'super_admin', function($q) {
                $q->where('company_id', Auth::user()->company_id);
            })->count(),
            'active_customers' => Customer::when(Auth::user()->role !== 'super_admin', function($q) {
                $q->where('company_id', Auth::user()->company_id);
            })->where('status', 'active')->count(),
            'inactive_customers' => Customer::when(Auth::user()->role !== 'super_admin', function($q) {
                $q->where('company_id', Auth::user()->company_id);
            })->where('status', 'inactive')->count(),
            'new_this_month' => Customer::when(Auth::user()->role !== 'super_admin', function($q) {
                $q->where('company_id', Auth::user()->company_id);
            })->whereMonth('created_at', now()->month)
              ->whereYear('created_at', now()->year)
              ->count(),
            'total_projects' => Project::when(Auth::user()->role !== 'super_admin', function($q) {
                $q->where('company_id', Auth::user()->company_id);
            })->count(),
            'total_revenue' => Project::when(Auth::user()->role !== 'super_admin', function($q) {
                $q->where('company_id', Auth::user()->company_id);
            })->sum('monthly_fee')
        ];

        return view('customers.index', compact('customers', 'stats'));
    }

    public function create()
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators can create customers.');
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

        return view('customers.create', compact('templates'));
    }

    public function store(Request $request)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators can create customers.');
        }

        // Validation
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:255',
            'street' => 'nullable|string|max:255',
            'addition' => 'nullable|string|max:50',
            'zip_code' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'contact_person' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'company_id' => Auth::user()->role === 'super_admin' ? 'required|exists:companies,id' : 'nullable'
        ]);

        try {
            DB::beginTransaction();

            // Customer aanmaken met company_id
            $customerData = [
                'company_id' => Auth::user()->role === 'super_admin' && $request->has('company_id') 
                    ? $request->company_id 
                    : Auth::user()->company_id,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'street' => $request->street,
                'addition' => $request->addition,
                'zip_code' => $request->zip_code,
                'city' => $request->city,
                'country' => $request->country ?: 'Netherlands',
                'contact_person' => $request->contact_person,
                'company' => $request->company,
                'notes' => $request->notes,
                'status' => $request->status,
                'is_active' => $request->status === 'active' ? 1 : 0,
                'address' => null // Clear old address field
            ];
            
            $customer = Customer::create($customerData);

            // Log the creation activity with full details
            $creationDetails = [];
            $creationDetails['Name'] = ['old' => null, 'new' => $customer->name];
            if ($customer->email) $creationDetails['Email'] = ['old' => null, 'new' => $customer->email];
            if ($customer->phone) $creationDetails['Phone'] = ['old' => null, 'new' => $customer->phone];
            if ($customer->street) $creationDetails['Street'] = ['old' => null, 'new' => $customer->street];
            if ($customer->addition) $creationDetails['Addition'] = ['old' => null, 'new' => $customer->addition];
            if ($customer->zip_code) $creationDetails['Zip Code'] = ['old' => null, 'new' => $customer->zip_code];
            if ($customer->city) $creationDetails['City'] = ['old' => null, 'new' => $customer->city];
            if ($customer->country) $creationDetails['Country'] = ['old' => null, 'new' => $customer->country];
            if ($customer->contact_person) $creationDetails['Contact Person'] = ['old' => null, 'new' => $customer->contact_person];
            if ($customer->company) $creationDetails['Company Name'] = ['old' => null, 'new' => $customer->company];
            if ($customer->notes) $creationDetails['Notes'] = ['old' => null, 'new' => $customer->notes];
            $creationDetails['Status'] = ['old' => null, 'new' => $customer->status];
            $creationDetails['Active'] = ['old' => null, 'new' => $customer->is_active ? 'Yes' : 'No'];
            
            // Get company name for better logging
            if ($customer->company_id) {
                $companyName = \App\Models\Company::find($customer->company_id)?->name;
                if ($companyName) {
                    $creationDetails['Managing Company'] = ['old' => null, 'new' => $companyName];
                }
            }
            
            CustomerActivity::log(
                $customer->id,
                'created',
                'created new customer',
                $creationDetails
            );

            DB::commit();

            return redirect()->route('customers.show', $customer)
                ->with('success', 'Customer created successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Error creating customer: ' . $e->getMessage());
        }
    }

    public function show(Customer $customer)
    {
        // Authorization check - gebruikers kunnen alleen eigen company customers zien
        if (Auth::user()->role !== 'super_admin' && $customer->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only view customers from your own company.');
        }

        // Eager loading met relationships
        $customer->load(['companyRelation', 'projects.companies']);

        // Customer statistics berekenen
        $stats = [
            'total_projects' => $customer->projects()->count(),
            'active_projects' => $customer->projects()->where('status', 'active')->count(),
            'completed_projects' => $customer->projects()->where('status', 'completed')->count(),
            'total_value' => $customer->projects()->sum('total_value') ?? 0,
            'monthly_recurring' => $customer->projects()->whereNotNull('monthly_fee')->sum('monthly_fee') ?? 0
        ];

        // Recent projects voor timeline
        $recentProjects = $customer->projects()
            ->with(['companies'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('customers.show', compact('customer', 'stats', 'recentProjects'));
    }

    public function edit(Customer $customer)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators can edit customers.');
        }

        // Company isolation check
        if (Auth::user()->role !== 'super_admin' && $customer->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only edit customers from your own company.');
        }

        // Als nieuwe adresvelden leeg zijn maar oude address bestaat, migreer het
        if ($customer->address && !$customer->street && !$customer->city) {
            // Probeer het oude adres te parsen
            $addressLines = explode(',', $customer->address);
            
            if (count($addressLines) > 0) {
                // Eerste deel is waarschijnlijk straat
                $customer->street = trim($addressLines[0]);
                
                // Als er meer delen zijn, probeer stad te vinden
                if (count($addressLines) > 1) {
                    // Laatste deel is vaak stad
                    $lastPart = trim(end($addressLines));
                    
                    // Check of het een postcode + stad combinatie is
                    if (preg_match('/^(\d{4}\s?[A-Z]{2})\s+(.+)$/i', $lastPart, $matches)) {
                        $customer->zip_code = $matches[1];
                        $customer->city = $matches[2];
                    } else {
                        $customer->city = $lastPart;
                    }
                }
            }
            
            // Zet default country als die leeg is
            if (!$customer->country) {
                $customer->country = 'Netherlands';
            }
        }

        // Plugin system removed - always show companies
        $isCompaniesPluginActive = true;
        
        // Get available companies based on plugin status
        $companies = collect();
        $defaultCompany = null;
        
        if ($isCompaniesPluginActive) {
            // Multi-company mode: show available companies
            $companies = \App\Models\Company::where('is_active', true)
                ->when(Auth::user()->role !== 'super_admin', function($q) {
                    $q->where('id', Auth::user()->company_id);
                })
                ->orderBy('name')
                ->get();
        } else {
            // Single company mode: auto-select default company
            $defaultCompany = \App\Models\Company::where('is_active', true)
                ->when(Auth::user()->role !== 'super_admin', function($q) {
                    $q->where('id', Auth::user()->company_id);
                })
                ->first();
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

        return view('customers.edit', compact(
            'customer', 
            'templates', 
            'isCompaniesPluginActive', 
            'companies', 
            'defaultCompany'
        ));
    }

    public function update(Request $request, Customer $customer)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators can update customers.');
        }

        // Company isolation check
        if (Auth::user()->role !== 'super_admin' && $customer->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only update customers from your own company.');
        }

        // Validation
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:255',
            'street' => 'nullable|string|max:255',
            'addition' => 'nullable|string|max:50',
            'zip_code' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'contact_person' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'companies' => 'array',
            'companies.*' => 'exists:companies,id',
            'company_primary' => 'array'
        ]);

        try {
            DB::beginTransaction();

            // Track changes for activity log
            $oldValues = $customer->toArray();
            $oldCompanies = $customer->companies->pluck('id')->toArray();
            $changes = [];

            // Customer bijwerken
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'street' => $request->street,
                'addition' => $request->addition,
                'zip_code' => $request->zip_code,
                'city' => $request->city,
                'country' => $request->country ?: 'Netherlands',
                'contact_person' => $request->contact_person,
                'company' => $request->company,
                'notes' => $request->notes,
                'status' => $request->status,
                'is_active' => $request->status === 'active' ? 1 : 0,
                'address' => null // Clear old address field
            ];

            // Keep legacy company_id for backwards compatibility
            // Primary company will be set in company_id field
            $primaryCompany = null;
            
            // Sync managing companies
            if (Auth::user()->role === 'super_admin' && $request->has('companies')) {
                $syncData = [];
                foreach ($request->companies as $companyId) {
                    $isPrimary = isset($request->company_primary[$companyId]) && $request->company_primary[$companyId] == '1';
                    
                    if ($isPrimary) {
                        $primaryCompany = $companyId;
                    }
                    
                    $syncData[$companyId] = [
                        'is_primary' => $isPrimary,
                        'role' => null,
                        'notes' => null
                    ];
                }
                
                // Sync the companies
                $customer->companies()->sync($syncData);
                
                // Update legacy company_id field with primary company
                if ($primaryCompany) {
                    $updateData['company_id'] = $primaryCompany;
                }
            }

            $customer->update($updateData);

            // Track field changes with better formatting
            $fieldsToTrack = [
                'name' => 'Name',
                'email' => 'Email',
                'phone' => 'Phone',
                'street' => 'Street',
                'addition' => 'Addition',
                'zip_code' => 'Zip Code',
                'city' => 'City',
                'country' => 'Country',
                'contact_person' => 'Contact Person',
                'company' => 'Company Name',
                'notes' => 'Notes',
                'status' => 'Status',
                'is_active' => 'Active Status'
            ];
            
            foreach ($fieldsToTrack as $field => $label) {
                $oldValue = $oldValues[$field] ?? null;
                $newValue = $customer->$field ?? null;
                
                // Skip if values are the same
                if ($oldValue == $newValue) {
                    continue;
                }
                
                // Format boolean values
                if ($field === 'is_active') {
                    $oldValue = $oldValue ? 'Yes' : 'No';
                    $newValue = $newValue ? 'Yes' : 'No';
                }
                
                // Handle empty values display
                if ($oldValue === null || $oldValue === '') {
                    $oldValue = '(empty)';
                }
                if ($newValue === null || $newValue === '') {
                    $newValue = '(empty)';
                }
                
                $changes[$label] = [
                    'old' => $oldValue,
                    'new' => $newValue
                ];
            }

            // Log all field changes if any
            if (!empty($changes)) {
                // Log each field change separately for clarity
                foreach ($changes as $fieldName => $fieldData) {
                    // Create descriptive message with old and new values
                    $oldValue = $fieldData['old'];
                    $newValue = $fieldData['new'];
                    
                    // Format the description based on the field
                    if ($fieldName === 'Status') {
                        $description = 'updated status from ' . $oldValue . ' to ' . $newValue;
                    } elseif ($fieldName === 'Active Status') {
                        $description = 'updated active status from ' . $oldValue . ' to ' . $newValue;
                    } else {
                        // For other fields, show the actual values
                        $description = 'updated ' . strtolower($fieldName);
                        if ($oldValue !== '(empty)' && $newValue !== '(empty)') {
                            $description .= ' from "' . $oldValue . '" to "' . $newValue . '"';
                        } elseif ($oldValue === '(empty)') {
                            $description .= ' to "' . $newValue . '"';
                        } elseif ($newValue === '(empty)') {
                            $description .= ' from "' . $oldValue . '" to empty';
                        }
                    }
                    
                    CustomerActivity::log(
                        $customer->id,
                        'updated',
                        $description,
                        [$fieldName => $fieldData]
                    );
                }
            }

            // Track company changes if super_admin
            if (Auth::user()->role === 'super_admin' && $request->has('companies')) {
                $newCompanies = $request->companies;
                $addedCompanies = array_diff($newCompanies, $oldCompanies);
                $removedCompanies = array_diff($oldCompanies, $newCompanies);
                
                // Log company additions
                foreach ($addedCompanies as $companyId) {
                    $company = \App\Models\Company::find($companyId);
                    if ($company) {
                        CustomerActivity::log(
                            $customer->id,
                            'company_added',
                            'added managing company',
                            ['Managing Company' => ['old' => null, 'new' => $company->name]]
                        );
                    }
                }
                
                // Log company removals
                foreach ($removedCompanies as $companyId) {
                    $company = \App\Models\Company::find($companyId);
                    if ($company) {
                        CustomerActivity::log(
                            $customer->id,
                            'company_removed',
                            'removed managing company',
                            ['Managing Company' => ['old' => $company->name, 'new' => null]]
                        );
                    }
                }
            }

            DB::commit();

            return redirect()->route('customers.show', $customer)
                ->with('success', 'Customer updated successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Error updating customer: ' . $e->getMessage());
        }
    }

    public function destroy(Customer $customer)
    {
        // Authorization check - alleen admin en super_admin kunnen verwijderen
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can delete customers.');
        }

        // Company isolation check
        if (Auth::user()->role !== 'super_admin' && $customer->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only delete customers from your own company.');
        }

        try {
            DB::beginTransaction();

            // Check of customer projecten heeft
            if ($customer->projects()->count() > 0) {
                return back()->with('error', 'Cannot delete customer with existing projects. Please remove or reassign projects first.');
            }

            // Collect deletion details for activity log
            $deletionDetails = [];
            $deletionDetails['Name'] = ['old' => $customer->name, 'new' => null];
            $deletionDetails['Email'] = ['old' => $customer->email, 'new' => null];
            if ($customer->phone) $deletionDetails['Phone'] = ['old' => $customer->phone, 'new' => null];
            if ($customer->company) $deletionDetails['Company'] = ['old' => $customer->company, 'new' => null];
            if ($customer->contact_person) $deletionDetails['Contact Person'] = ['old' => $customer->contact_person, 'new' => null];
            if ($customer->city) $deletionDetails['City'] = ['old' => $customer->city, 'new' => null];
            $deletionDetails['Status'] = ['old' => $customer->status, 'new' => null];
            
            // Log the deletion activity
            CustomerActivity::log(
                $customer->id,
                'deleted',
                'deleted customer',
                $deletionDetails
            );

            // Customer verwijderen (soft delete)
            $customer->delete();

            DB::commit();

            return redirect()->route('customers.index')
                ->with('success', 'Customer deleted successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error deleting customer: ' . $e->getMessage());
        }
    }

    /**
     * Bulk operations voor customers
     */
    public function bulkUpdate(Request $request)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can perform bulk operations.');
        }

        $request->validate([
            'customer_ids' => 'required|array',
            'customer_ids.*' => 'exists:customers,id',
            'action' => 'required|in:activate,deactivate,delete'
        ]);

        try {
            DB::beginTransaction();

            $customers = Customer::whereIn('id', $request->customer_ids);
            
            // Company isolation voor non-super admins
            if (Auth::user()->role !== 'super_admin') {
                $customers->where('company_id', Auth::user()->company_id);
            }

            switch ($request->action) {
                case 'activate':
                    // Get customers before update for logging
                    $customersToUpdate = $customers->get();
                    foreach ($customersToUpdate as $customer) {
                        if ($customer->status !== 'active') {
                            CustomerActivity::log(
                                $customer->id,
                                'activated',
                                'activated customer',
                                ['Status' => ['old' => 'inactive', 'new' => 'active']]
                            );
                        }
                    }
                    $customers->update(['status' => 'active', 'is_active' => 1]);
                    $message = 'Customers activated successfully!';
                    break;
                case 'deactivate':
                    // Get customers before update for logging
                    $customersToUpdate = $customers->get();
                    foreach ($customersToUpdate as $customer) {
                        if ($customer->status !== 'inactive') {
                            CustomerActivity::log(
                                $customer->id,
                                'deactivated',
                                'deactivated customer',
                                ['Status' => ['old' => 'active', 'new' => 'inactive']]
                            );
                        }
                    }
                    $customers->update(['status' => 'inactive', 'is_active' => 0]);
                    $message = 'Customers deactivated successfully!';
                    break;
                case 'delete':
                    // Check for projects
                    $hasProjects = $customers->whereHas('projects')->exists();
                    if ($hasProjects) {
                        return back()->with('error', 'Cannot delete customers with existing projects.');
                    }
                    
                    // Log deletion for each customer
                    $customersToDelete = $customers->get();
                    foreach ($customersToDelete as $customer) {
                        CustomerActivity::log(
                            $customer->id,
                            'deleted',
                            'deleted customer',
                            ['Name' => ['old' => $customer->name, 'new' => null]]
                        );
                    }
                    
                    $customers->delete();
                    $message = 'Customers deleted successfully!';
                    break;
            }

            DB::commit();

            return redirect()->route('customers.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error performing bulk operation: ' . $e->getMessage());
        }
    }

    /**
     * Export customers naar Excel/CSV
     */
    public function export(Request $request)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Insufficient permissions to export customers.');
        }

        // Query met filters
        $query = Customer::query();
        
        if (Auth::user()->role !== 'super_admin') {
            $query->where('company_id', Auth::user()->company_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $customers = $query->with(['companyRelation'])->get();

        $filename = 'customers_export_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($customers) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID', 'Name', 'Email', 'Phone', 'Company', 
                'Contact Person', 'Status', 'Projects Count', 
                'Created At', 'Company Name'
            ]);

            // Data rows
            foreach ($customers as $customer) {
                fputcsv($file, [
                    $customer->id,
                    $customer->name,
                    $customer->email,
                    $customer->phone,
                    $customer->company,
                    $customer->contact_person,
                    $customer->status,
                    $customer->projects()->count(),
                    $customer->created_at->format('Y-m-d H:i:s'),
                    $customer->companyRelation->name ?? 'N/A'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}