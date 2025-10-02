<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyActivity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CompanyController extends Controller
{
    /**
     * Check of multi-company mode actief is
     */
    private function isMultiCompanyMode(): bool
    {
        // Plugin system removed - always enable multi-company mode
        return true;
    }

    /**
     * Get maximum aantal companies
     */
    private function getMaxCompanies(): int
    {
        // Als companies plugin actief is, dan unlimited companies
        if ($this->isMultiCompanyMode()) {
            return PHP_INT_MAX; // Unlimited
        }
        
        return 1; // Single company mode
    }

    /**
     * Check of nieuwe company toegestaan is
     */
    private function canCreateNewCompany(): bool
    {
        if ($this->isMultiCompanyMode()) {
            return true; // In multi-company mode is alles toegestaan
        }
        
        // In single company mode: check of er al een company bestaat
        return Company::count() < $this->getMaxCompanies();
    }
    /**
     * Toon overzicht van alle companies met filtering en stats
     */
    public function index(Request $request)
    {
        // Authorization check direct in method
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can manage companies.');
        }

        $user = Auth::user();
        
        // Check of multi-company mode actief is
        $isMultiCompanyMode = $this->isMultiCompanyMode();
        $canCreateNewCompany = $this->canCreateNewCompany();
        
        // Query building met customers relatie
        $query = Company::with(['users', 'customers']);

        // Super admin ziet alle companies, admin alleen eigen company
        if ($user->role === 'admin') {
            $query->where('id', $user->company_id);
        }

        // Search filtering
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('vat_number', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        // Status filtering
        if ($request->filled('status')) {
            $active = $request->status === 'active' ? 1 : 0;
            $query->where('is_active', $active);
        }

        // Role filtering
        if ($request->filled('role')) {
            if ($request->role === 'main_invoicing') {
                $query->where('is_main_invoicing', true);
            } elseif ($request->role === 'subcontractor') {
                $query->where('is_main_invoicing', false);
            }
        }

        $companies = $query->orderBy('name')->get();

        // Calculate metrics met echte data
        $companies->each(function($company) {
            // Nu kunnen we echte revenue berekenen via customers
            $company->monthlyRevenue = $company->customers->sum(function($customer) {
                return $customer->total_revenue ?? 0;
            });
        });

        $pageTitle = 'Companies';
        $pageDescription = $isMultiCompanyMode 
            ? 'Manage all companies (BV\'s) in your organization'
            : 'Manage your company information';

        return view('companies.index', compact(
            'companies', 
            'pageTitle', 
            'pageDescription',
            'isMultiCompanyMode',
            'canCreateNewCompany'
        ));
    }

    /**
     * Toon formulier voor nieuwe company
     */
    public function create()
    {
        // Authorization check
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can manage companies.');
        }

        // Check of nieuwe company toegestaan is
        if (!$this->canCreateNewCompany()) {
            return redirect()->route('companies.index')
                ->with('error', 'Only one company is allowed. Enable multi-company mode to create more companies.');
        }

        $pageTitle = 'New Company';
        $pageDescription = 'Add a new company to your organization';

        return view('companies.create', compact('pageTitle', 'pageDescription'));
    }

    /**
     * Sla nieuwe company op in database
     */
    public function store(Request $request)
    {
        // Authorization check
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can manage companies.');
        }

        // Check of nieuwe company toegestaan is
        if (!$this->canCreateNewCompany()) {
            return redirect()->route('companies.index')
                ->with('error', 'Only one company is allowed. Enable multi-company mode to create more companies.');
        }

        // Basic validation
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'default_hourly_rate' => 'nullable|numeric|min:0|max:9999.99',
            'vat_number' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Data array - ALLEEN kolommen die echt bestaan in de tabel
            $data = [
                'name' => $request->name,
                'vat_number' => $request->vat_number,
                'address' => $request->address,
                'street' => $request->street,
                'house_number' => $request->house_number,
                'addition' => $request->addition,
                'postal_code' => $request->postal_code,
                'city' => $request->city,
                'country' => $request->country ?? 'Netherlands',
                'email' => $request->email,
                'phone' => $request->phone,
                'website' => $request->website,
                'default_hourly_rate' => $request->default_hourly_rate ?? 75.00,
                'is_main_invoicing' => $request->boolean('is_main_invoicing'),
                'is_active' => 1,
                'bank_details' => json_encode([]),
                'invoice_settings' => json_encode([]),
            ];

            // Create company
            $company = Company::create($data);

            // Log the creation activity
            $creationDetails = [];
            $creationDetails['Name'] = ['old' => null, 'new' => $company->name];
            if ($company->email) $creationDetails['Email'] = ['old' => null, 'new' => $company->email];
            if ($company->phone) $creationDetails['Phone'] = ['old' => null, 'new' => $company->phone];
            if ($company->vat_number) $creationDetails['VAT Number'] = ['old' => null, 'new' => $company->vat_number];
            if ($company->website) $creationDetails['Website'] = ['old' => null, 'new' => $company->website];
            if ($company->address) $creationDetails['Address'] = ['old' => null, 'new' => $company->address];
            $creationDetails['Hourly Rate'] = ['old' => null, 'new' => $company->default_hourly_rate];
            $creationDetails['Active Status'] = ['old' => null, 'new' => $company->is_active];
            
            CompanyActivity::log(
                $company->id,
                'created',
                'created new company',
                $creationDetails
            );

            DB::commit();

            return redirect()->route('companies.index')
                ->with('success', 'Company created successfully');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Error creating company: ' . $e->getMessage());
        }
    }

    /**
     * Toon details van een specifieke company
     */
    public function show(Company $company)
    {
        // Authorization check
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can manage companies.');
        }

        // Load relationships
        $company->load(['users', 'customers']);

        // Calculate comprehensive stats
        $stats = [
            'total_users' => $company->users->count(),
            'active_users' => $company->users->where('email_verified_at', '!=', null)->count(),
            'total_customers' => $company->customers->count(),
            'active_customers' => $company->customers->where('is_active', true)->count(),
            'monthly_revenue' => $company->monthly_revenue ?? 0,
        ];

        $pageTitle = $company->name;
        $pageDescription = 'Company details and overview';

        return view('companies.show', compact('company', 'stats', 'pageTitle', 'pageDescription'));
    }

    /**
     * Toon edit formulier voor company
     */
    public function edit(Company $company)
    {
        // Authorization check
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can manage companies.');
        }

        $pageTitle = 'Edit Company';
        $pageDescription = 'Edit company: ' . $company->name;

        return view('companies.edit', compact('company', 'pageTitle', 'pageDescription'));
    }

    /**
     * Show company modal (AJAX)
     */
    public function showModal(Company $company)
    {
        // Authorization check
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            // Load relationships for stats
            $company->load(['users', 'customers']);

            $html = view('companies.show-modal', compact('company'))->render();

            return response()->json([
                'success' => true,
                'html' => $html
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load company details'
            ], 500);
        }
    }

    /**
     * Edit company modal (AJAX)
     */
    public function editModal(Company $company)
    {
        // Authorization check
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            $html = view('companies.edit-modal', compact('company'))->render();

            return response()->json([
                'success' => true,
                'html' => $html
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load edit form'
            ], 500);
        }
    }

    /**
     * Update company in database
     */
    public function update(Request $request, Company $company)
    {
        // Authorization check
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can manage companies.');
        }

        // Basic validation
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string|max:1000',
            'default_hourly_rate' => 'nullable|numeric|min:0|max:9999.99',
            'vat_rate' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string|max:2000',
            'bank_details' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            // Track changes for activity log
            $oldValues = $company->toArray();
            $changes = [];

            // Prepare bank details
            $bankDetails = [];
            if ($request->has('bank_details')) {
                $bankDetails = array_filter($request->bank_details); // Remove empty values
            }

            // Prepare update data
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'website' => $request->website,
                'address' => $request->address,
                'default_hourly_rate' => $request->default_hourly_rate,
                'vat_rate' => $request->vat_rate,
                'status' => $request->status,
                'notes' => $request->notes,
                'bank_details' => $bankDetails,
            ];

            $company->update($data);

            // Track field changes with better null handling
            $fieldsToTrack = [
                'name' => 'Name',
                'vat_number' => 'VAT Number',
                'address' => 'Address',
                'street' => 'Street',
                'house_number' => 'House Number',
                'addition' => 'Addition',
                'postal_code' => 'Postal Code',
                'city' => 'City',
                'country' => 'Country',
                'email' => 'Email',
                'phone' => 'Phone',
                'website' => 'Website',
                'default_hourly_rate' => 'Hourly Rate',
                'is_main_invoicing' => 'Main Invoicing',
                'is_active' => 'Active Status'
            ];
            
            foreach ($fieldsToTrack as $field => $label) {
                $oldValue = $oldValues[$field] ?? null;
                $newValue = $company->$field ?? null;
                
                // Skip if values are the same
                if ($oldValue == $newValue) {
                    continue;
                }
                
                $changes[$label] = [
                    'old' => $oldValue,
                    'new' => $newValue
                ];
            }

            // Log all field changes if any
            if (!empty($changes)) {
                $description = count($changes) === 1 
                    ? 'updated ' . strtolower(array_key_first($changes))
                    : 'updated ' . count($changes) . ' fields';
                    
                CompanyActivity::log(
                    $company->id,
                    'updated',
                    $description,
                    $changes
                );
            }

            DB::commit();

            // Handle AJAX requests
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Company updated successfully'
                ]);
            }

            return redirect()->route('companies.show', $company)
                ->with('success', 'Company updated successfully');

        } catch (\Exception $e) {
            DB::rollback();

            // Handle AJAX requests
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating company: ' . $e->getMessage()
                ], 500);
            }

            return back()->withInput()
                ->with('error', 'Error updating company: ' . $e->getMessage());
        }
    }

    /**
     * Verwijder company (soft delete)
     */
    /**
     * Get users from a specific company (for AJAX)
     */
    public function getUsers(Company $company)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            // Get all active users from this company
            $users = User::where('company_id', $company->id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'role']);

            return response()->json([
                'success' => true,
                'users' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load users'
            ], 500);
        }
    }

    /**
     * Show company activity log
     */
    public function activity(Company $company)
    {
        // Authorization check
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can view activity logs.');
        }

        // Get all activities for this company with user information
        $activities = CompanyActivity::where('company_id', $company->id)
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        $pageTitle = 'Activity Log - ' . $company->name;
        $pageDescription = 'Complete activity history and change log';

        return view('companies.activity', compact('company', 'activities', 'pageTitle', 'pageDescription'));
    }

    public function destroy(Company $company)
    {
        // Authorization check
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can manage companies.');
        }

        try {
            DB::beginTransaction();

            // Check of company veilig verwijderd kan worden
            if (!$company->canBeDeletedBy(Auth::user())) {
                return back()->with('error', 'Cannot delete company: it has active projects or users.');
            }

            // Simple delete
            $company->delete();

            DB::commit();

            return redirect()->route('companies.index')
                ->with('success', 'Company deleted successfully');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error deleting company: ' . $e->getMessage());
        }
    }

    /**
     * API endpoint voor company financials (gebruikt door dashboard)
     */
    public function financials(Company $company)
    {
        // Authorization check
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        return response()->json($company->getFinancialSummary());
    }

    /**
     * Bulk operations voor companies
     */
    public function bulkAction(Request $request)
    {
        // Authorization check
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can manage companies.');
        }

        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'company_ids' => 'required|array',
            'company_ids.*' => 'exists:companies,id'
        ]);

        try {
            DB::beginTransaction();

            $companies = Company::whereIn('id', $request->company_ids)->get();
            $count = 0;

            foreach ($companies as $company) {
                switch ($request->action) {
                    case 'activate':
                        $company->update(['is_active' => true]);
                        $count++;
                        break;
                    case 'deactivate':
                        $company->update(['is_active' => false]);
                        $count++;
                        break;
                    case 'delete':
                        if ($company->canBeDeletedBy(Auth::user())) {
                            $company->delete();
                            $count++;
                        }
                        break;
                }
            }

            DB::commit();

            $actionText = match($request->action) {
                'activate' => 'activated',
                'deactivate' => 'deactivated',
                'delete' => 'deleted',
            };

            return back()->with('success', "{$count} companies {$actionText} successfully");

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error performing bulk action: ' . $e->getMessage());
        }
    }

    /**
     * Export companies naar Excel/CSV
     */
    public function export(Request $request)
    {
        // Authorization check
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can manage companies.');
        }

        $user = Auth::user();
        
        // Get companies data
        $query = Company::with(['users', 'customers']);

        if ($user->role === 'admin') {
            $query->where('id', $user->company_id);
        }

        $companies = $query->orderBy('name')->get();

        // Create CSV content
        $csv = "Name,VAT Number,Email,Phone,Website,Default Rate,Status,Role,Users,Customers,Revenue\n";
        
        foreach ($companies as $company) {
            $csv .= '"' . $company->name . '",';
            $csv .= '"' . ($company->vat_number ?? '') . '",';
            $csv .= '"' . ($company->email ?? '') . '",';
            $csv .= '"' . ($company->phone ?? '') . '",';
            $csv .= '"' . ($company->website ?? '') . '",';
            $csv .= '"€' . number_format($company->default_hourly_rate, 2) . '",';
            $csv .= '"' . ($company->is_active ? 'Active' : 'Inactive') . '",';
            $csv .= '"' . ($company->is_main_invoicing ? 'Main Invoicing' : 'Subcontractor') . '",';
            $csv .= $company->users->count() . ',';
            $csv .= $company->customers->count() . ',';
            $csv .= '"€' . number_format($company->monthly_revenue, 2) . '"';
            $csv .= "\n";
        }

        // Return CSV download
        $filename = 'companies_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Company Settings - Voor single company mode (altijd beschikbaar)
     */
    public function settings()
    {
        // Authorization check
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can manage company settings.');
        }

        $user = Auth::user();

        // Get eerste company of de company van de gebruiker
        $company = null;
        if ($user->role === 'super_admin') {
            $company = Company::first(); // Eerste company in systeem
        } else {
            $company = Company::find($user->company_id); // Eigen company
        }

        if (!$company) {
            return redirect()->route('dashboard')
                ->with('error', 'No company found. Please create a company first.');
        }

        // Companies functionality is always active
        $isCompaniesPluginActive = true;

        $pageTitle = 'Company Settings';
        $pageDescription = 'Manage your company information for invoicing and legal purposes';

        return view('companies.settings', compact('company', 'pageTitle', 'pageDescription', 'isCompaniesPluginActive'));
    }

    /**
     * Update Company Settings
     */
    public function updateSettings(Request $request)
    {
        // Authorization check
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can manage company settings.');
        }

        $user = Auth::user();

        // Get company to update
        $company = null;
        if ($user->role === 'super_admin') {
            $company = Company::first();
        } else {
            $company = Company::find($user->company_id);
        }

        if (!$company) {
            return redirect()->route('dashboard')
                ->with('error', 'No company found.');
        }

        // Validation
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'vat_number' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'street' => 'nullable|string|max:255',
            'house_number' => 'nullable|string|max:20',
            'addition' => 'nullable|string|max:20',
            'postal_code' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'default_hourly_rate' => 'nullable|numeric|min:0|max:9999.99',
        ]);

        try {
            DB::beginTransaction();

            // Update company data
            $company->update([
                'name' => $request->name,
                'email' => $request->email,
                'vat_number' => $request->vat_number,
                'phone' => $request->phone,
                'website' => $request->website,
                'street' => $request->street,
                'house_number' => $request->house_number,
                'addition' => $request->addition,
                'postal_code' => $request->postal_code,
                'city' => $request->city,
                'country' => $request->country ?? 'Netherlands',
                'default_hourly_rate' => $request->default_hourly_rate ?? 75.00,
            ]);

            // Log activity - companies functionality is always active
            CompanyActivity::create([
                'company_id' => $company->id,
                'user_id' => Auth::id(),
                'activity_type' => 'updated',
                'description' => 'updated company settings',
                'old_values' => json_encode([]),
                'new_values' => json_encode([]),
                'ip_address' => $request->ip(),
            ]);

            DB::commit();

            return redirect()->route('company.settings')
                ->with('success', 'Company settings updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Company settings update error', [
                'company_id' => $company->id,
                'error' => $e->getMessage()
            ]);
            return back()->withInput()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }
}