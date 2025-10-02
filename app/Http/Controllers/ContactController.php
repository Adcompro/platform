<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Customer;
use App\Models\Company;
use App\Models\ContactActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Authorization check - alleen admin en super_admin kunnen alle contacten zien
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Insufficient permissions to view contacts.');
        }

        // Query building met filters
        $query = Contact::with(['customer', 'company', 'companies']);
        
        // Company filtering voor non-super admins
        if (Auth::user()->role !== 'super_admin') {
            // Toon alleen contacten van customers die bij de eigen company horen
            $query->whereHas('customer', function($q) {
                $q->where('company_id', Auth::user()->company_id);
            });
        }

        // Search functionaliteit
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%')
                  ->orWhere('position', 'like', '%' . $request->search . '%')
                  ->orWhereHas('customer', function($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        // Customer filter
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Company filter
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Primary contacts only
        if ($request->filled('primary_only')) {
            $query->where('is_primary', true);
        }

        $contacts = $query->orderBy('name')->paginate(20);

        // Statistics
        $stats = [
            'total_contacts' => Contact::whereHas('customer', function($q) {
                if (Auth::user()->role !== 'super_admin') {
                    $q->where('company_id', Auth::user()->company_id);
                }
            })->count(),
            'active_contacts' => Contact::whereHas('customer', function($q) {
                if (Auth::user()->role !== 'super_admin') {
                    $q->where('company_id', Auth::user()->company_id);
                }
            })->where('is_active', true)->count(),
            'primary_contacts' => Contact::whereHas('customer', function($q) {
                if (Auth::user()->role !== 'super_admin') {
                    $q->where('company_id', Auth::user()->company_id);
                }
            })->where('is_primary', true)->count(),
            'new_this_month' => Contact::whereHas('customer', function($q) {
                if (Auth::user()->role !== 'super_admin') {
                    $q->where('company_id', Auth::user()->company_id);
                }
            })->whereMonth('created_at', now()->month)
              ->whereYear('created_at', now()->year)
              ->count()
        ];

        // Get customers voor dropdown filter
        $customers = Customer::when(Auth::user()->role !== 'super_admin', function($q) {
            $q->where('company_id', Auth::user()->company_id);
        })->orderBy('name')->get();

        // Get companies voor dropdown filter
        $companies = Company::when(Auth::user()->role !== 'super_admin', function($q) {
            $q->where('id', Auth::user()->company_id);
        })->orderBy('name')->get();

        return view('contacts.index', compact('contacts', 'stats', 'customers', 'companies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators can create contacts.');
        }

        // Get customers voor dropdown
        $customers = Customer::when(Auth::user()->role !== 'super_admin', function($q) {
            $q->where('company_id', Auth::user()->company_id);
        })->orderBy('name')->get();

        // Get companies voor dropdown
        $companies = Company::when(Auth::user()->role !== 'super_admin', function($q) {
            $q->where('id', Auth::user()->company_id);
        })->orderBy('name')->get();

        // Als customer_id is meegegeven in de URL (vanaf customer detail pagina)
        $selectedCustomer = null;
        if ($request->has('customer_id')) {
            $selectedCustomer = Customer::find($request->customer_id);
            // Controleer of user toegang heeft tot deze customer
            if ($selectedCustomer && Auth::user()->role !== 'super_admin' && $selectedCustomer->company_id !== Auth::user()->company_id) {
                abort(403, 'Access denied.');
            }
        }

        return view('contacts.create', compact('customers', 'companies', 'selectedCustomer'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators can create contacts.');
        }

        // Validation
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'is_primary' => 'boolean',
            'is_active' => 'boolean',
            'companies' => 'array',
            'companies.*' => 'exists:companies,id',
            'company_primary' => 'array'
        ]);

        // Controleer of user toegang heeft tot de customer
        $customer = Customer::find($request->customer_id);
        if (Auth::user()->role !== 'super_admin' && $customer->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only add contacts to customers from your own company.');
        }

        try {
            DB::beginTransaction();

            // Als dit contact als primary wordt aangemerkt, zet andere contacten op non-primary
            if ($request->input('is_primary', false)) {
                Contact::where('customer_id', $request->customer_id)
                    ->update(['is_primary' => false]);
            }

            $contact = Contact::create([
                'customer_id' => $request->customer_id,
                'company_id' => null, // Legacy field, we use pivot table now
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'position' => $request->position,
                'notes' => $request->notes,
                'is_primary' => $request->input('is_primary', false),
                'is_active' => $request->input('is_active', true)
            ]);

            // Sync companies met pivot data
            if ($request->has('companies')) {
                $syncData = [];
                $primaryCompany = null;
                
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
                
                $contact->companies()->sync($syncData);
                
                // Update legacy company_id field with primary company
                if ($primaryCompany) {
                    $contact->update(['company_id' => $primaryCompany]);
                }
            }

            // Log the creation activity with all details
            $creationDetails = [];
            if ($contact->name) $creationDetails['Name'] = ['old' => null, 'new' => $contact->name];
            if ($contact->email) $creationDetails['Email'] = ['old' => null, 'new' => $contact->email];
            if ($contact->phone) $creationDetails['Phone'] = ['old' => null, 'new' => $contact->phone];
            if ($contact->position) $creationDetails['Position'] = ['old' => null, 'new' => $contact->position];
            if ($contact->notes) $creationDetails['Notes'] = ['old' => null, 'new' => $contact->notes];
            $creationDetails['Primary Status'] = ['old' => null, 'new' => $contact->is_primary];
            $creationDetails['Active Status'] = ['old' => null, 'new' => $contact->is_active];
            
            // Add customer name
            $customer = Customer::find($contact->customer_id);
            if ($customer) {
                $creationDetails['Customer'] = ['old' => null, 'new' => $customer->name];
            }
            
            ContactActivity::log(
                $contact->id,
                'created',
                'created new contact',
                $creationDetails
            );

            // Log company additions
            if ($request->has('companies')) {
                foreach ($request->companies as $companyId) {
                    $company = Company::find($companyId);
                    if ($company) {
                        $isPrimary = isset($request->company_primary[$companyId]) && $request->company_primary[$companyId] == '1';
                        ContactActivity::log(
                            $contact->id,
                            'company_added',
                            'Company "' . $company->name . '" added' . ($isPrimary ? ' as primary' : ''),
                            ['company' => ['old' => null, 'new' => $company->name]]
                        );
                    }
                }
            }

            DB::commit();

            // Redirect naar customer detail als we van daar kwamen
            if ($request->has('redirect_to_customer')) {
                return redirect()->route('customers.show', $customer)
                    ->with('success', 'Contact created successfully!');
            }

            return redirect()->route('contacts.show', $contact)
                ->with('success', 'Contact created successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Error creating contact: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Contact $contact)
    {
        // Authorization check - gebruikers kunnen alleen contacten van eigen company customers zien
        if (Auth::user()->role !== 'super_admin') {
            if ($contact->customer->company_id !== Auth::user()->company_id) {
                abort(403, 'Access denied. You can only view contacts from your own company customers.');
            }
        }

        // Load relationships
        $contact->load(['customer', 'company']);

        return view('contacts.show', compact('contact'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contact $contact)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators can edit contacts.');
        }

        // Company isolation check
        if (Auth::user()->role !== 'super_admin' && $contact->customer->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only edit contacts from your own company customers.');
        }

        // Get customers voor dropdown
        $customers = Customer::when(Auth::user()->role !== 'super_admin', function($q) {
            $q->where('company_id', Auth::user()->company_id);
        })->orderBy('name')->get();

        // Get companies voor dropdown
        $companies = Company::when(Auth::user()->role !== 'super_admin', function($q) {
            $q->where('id', Auth::user()->company_id);
        })->orderBy('name')->get();

        return view('contacts.edit', compact('contact', 'customers', 'companies'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contact $contact)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators can update contacts.');
        }

        // Company isolation check
        if (Auth::user()->role !== 'super_admin' && $contact->customer->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only update contacts from your own company customers.');
        }

        // Validation
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'is_primary' => 'boolean',
            'is_active' => 'boolean',
            'companies' => 'array',
            'companies.*' => 'exists:companies,id',
            'company_primary' => 'array'
        ]);

        // Als customer verandert, check toegang
        if ($request->customer_id != $contact->customer_id) {
            $newCustomer = Customer::find($request->customer_id);
            if (Auth::user()->role !== 'super_admin' && $newCustomer->company_id !== Auth::user()->company_id) {
                abort(403, 'Access denied. You can only assign contacts to customers from your own company.');
            }
        }

        try {
            DB::beginTransaction();

            // Track changes for activity log
            $oldValues = $contact->toArray();
            $oldCompanies = $contact->companies->pluck('id')->toArray();
            $changes = [];

            // Als dit contact als primary wordt aangemerkt, zet andere contacten op non-primary
            if ($request->input('is_primary', false) && !$contact->is_primary) {
                Contact::where('customer_id', $request->customer_id)
                    ->where('id', '!=', $contact->id)
                    ->update(['is_primary' => false]);
            }

            // Update contact
            $contact->update([
                'customer_id' => $request->customer_id,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'position' => $request->position,
                'notes' => $request->notes,
                'is_primary' => $request->input('is_primary', false),
                'is_active' => $request->input('is_active', true)
            ]);

            // Track field changes with better null handling
            $fieldsToTrack = [
                'name' => 'Name',
                'email' => 'Email',
                'phone' => 'Phone',
                'position' => 'Position',
                'notes' => 'Notes',
                'is_primary' => 'Primary Status',
                'is_active' => 'Active Status',
                'customer_id' => 'Customer'
            ];
            
            foreach ($fieldsToTrack as $field => $label) {
                $oldValue = $oldValues[$field] ?? null;
                $newValue = $contact->$field ?? null;
                
                // Skip if values are the same
                if ($oldValue == $newValue) {
                    continue;
                }
                
                // Special handling for customer_id
                if ($field === 'customer_id') {
                    $oldCustomer = $oldValue ? Customer::find($oldValue) : null;
                    $newCustomer = $newValue ? Customer::find($newValue) : null;
                    if ($oldCustomer || $newCustomer) {
                        $changes[$label] = [
                            'old' => $oldCustomer ? $oldCustomer->name : null,
                            'new' => $newCustomer ? $newCustomer->name : null
                        ];
                    }
                } else {
                    $changes[$label] = [
                        'old' => $oldValue,
                        'new' => $newValue
                    ];
                }
            }

            // Log all field changes in one activity entry if any changes exist
            if (!empty($changes)) {
                $description = count($changes) === 1 
                    ? 'updated ' . strtolower(array_key_first($changes))
                    : 'updated ' . count($changes) . ' fields';
                    
                ContactActivity::log(
                    $contact->id,
                    'updated',
                    $description,
                    $changes
                );
            }

            // Sync companies met pivot data
            if (Auth::user()->role === 'super_admin' && $request->has('companies')) {
                $syncData = [];
                $primaryCompany = null;
                
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
                
                $contact->companies()->sync($syncData);
                
                // Track company changes
                $newCompanies = array_keys($syncData);
                $addedCompanies = array_diff($newCompanies, $oldCompanies);
                $removedCompanies = array_diff($oldCompanies, $newCompanies);
                
                // Log company additions
                foreach ($addedCompanies as $companyId) {
                    $company = Company::find($companyId);
                    if ($company) {
                        $isPrimary = $syncData[$companyId]['is_primary'];
                        ContactActivity::log(
                            $contact->id,
                            'company_added',
                            'Company "' . $company->name . '" added' . ($isPrimary ? ' as primary' : ''),
                            ['company' => ['old' => null, 'new' => $company->name]]
                        );
                    }
                }
                
                // Log company removals
                foreach ($removedCompanies as $companyId) {
                    $company = Company::find($companyId);
                    if ($company) {
                        ContactActivity::log(
                            $contact->id,
                            'company_removed',
                            'Company "' . $company->name . '" removed',
                            ['company' => ['old' => $company->name, 'new' => null]]
                        );
                    }
                }
                
                // Update legacy company_id field with primary company
                if ($primaryCompany) {
                    $contact->update(['company_id' => $primaryCompany]);
                } else {
                    $contact->update(['company_id' => null]);
                }
            }

            DB::commit();

            return redirect()->route('contacts.show', $contact)
                ->with('success', 'Contact updated successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Error updating contact: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contact $contact)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can delete contacts.');
        }

        // Company isolation check
        if (Auth::user()->role !== 'super_admin' && $contact->customer->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only delete contacts from your own company customers.');
        }

        try {
            $customerId = $contact->customer_id;
            
            // Log deletion activity before deleting
            ContactActivity::log(
                $contact->id,
                'deleted',
                'deleted contact "' . $contact->name . '"',
                [
                    'Name' => ['old' => $contact->name, 'new' => null],
                    'Customer' => ['old' => $contact->customer->name, 'new' => null]
                ]
            );
            
            $contact->delete();

            // Als we vanaf de customer pagina komen, redirect terug
            if (request()->has('redirect_to_customer')) {
                return redirect()->route('customers.show', $customerId)
                    ->with('success', 'Contact deleted successfully!');
            }

            return redirect()->route('contacts.index')
                ->with('success', 'Contact deleted successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting contact: ' . $e->getMessage());
        }
    }

    /**
     * Toggle primary status voor een contact
     */
    public function togglePrimary(Contact $contact)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied.');
        }

        // Company isolation check
        if (Auth::user()->role !== 'super_admin' && $contact->customer->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied.');
        }

        $contact->makePrimary();

        return back()->with('success', 'Contact set as primary successfully!');
    }
}