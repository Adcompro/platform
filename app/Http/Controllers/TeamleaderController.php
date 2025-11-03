<?php

namespace App\Http\Controllers;

use App\Services\TeamleaderService;
use App\Services\TeamleaderImportService;
use App\Services\TeamleaderProjectSyncService;
use App\Models\Setting;
use App\Models\Customer;
use App\Models\TeamleaderProject;
use App\Jobs\ImportTeamleaderCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TeamleaderController extends Controller
{
    /**
     * Show Teamleader import dashboard
     */
    public function index()
    {
        // Authorization check - SUPER ADMIN ONLY
        if (Auth::user()->role !== 'super_admin') {
            abort(403, 'Access denied. Only super administrators can manage Teamleader imports.');
        }

        // Check of Teamleader geautoriseerd is
        $isAuthorized = TeamleaderService::isTokenValid();
        $accessToken = Setting::get('teamleader_access_token');
        $tokenExpiresAt = Setting::get('teamleader_token_expires_at');

        // Als authorized, haal user info op
        $teamleaderUser = null;
        // TIJDELIJK UITGESCHAKELD - API call vertraagt pagina laden
        // if ($isAuthorized) {
        //     try {
        //         $teamleaderUser = TeamleaderService::getCurrentUser();
        //     } catch (\Exception $e) {
        //         Log::error('Failed to get Teamleader user info', ['error' => $e->getMessage()]);
        //     }
        // }

        return view('teamleader.index', compact('isAuthorized', 'accessToken', 'tokenExpiresAt', 'teamleaderUser'));
    }

    /**
     * Redirect naar Teamleader authorization page
     */
    public function authorize()
    {
        // Authorization check - SUPER ADMIN ONLY
        if (Auth::user()->role !== 'super_admin') {
            abort(403, 'Access denied. Only super administrators can authorize Teamleader.');
        }

        // Genereer random state voor security
        $state = Str::random(40);
        session(['teamleader_oauth_state' => $state]);

        // Redirect naar Teamleader authorization URL
        $authUrl = TeamleaderService::getAuthorizationUrl($state);

        Log::info('Redirecting to Teamleader authorization', ['url' => $authUrl]);

        return redirect($authUrl);
    }

    /**
     * OAuth callback from Teamleader
     */
    public function callback(Request $request)
    {
        // Verify state parameter (CSRF protection)
        if ($request->state !== session('teamleader_oauth_state')) {
            Log::warning('Teamleader OAuth state mismatch');
            return redirect()->route('teamleader.index')
                ->with('error', 'Invalid state parameter. Please try again.');
        }

        // Check for error
        if ($request->has('error')) {
            Log::warning('Teamleader OAuth error', ['error' => $request->error]);
            return redirect()->route('teamleader.index')
                ->with('error', 'Authorization denied: ' . $request->error);
        }

        // Exchange code for token
        try {
            $code = $request->code;
            $tokenData = TeamleaderService::exchangeCodeForToken($code);

            Log::info('Teamleader authorization successful', [
                'expires_in' => $tokenData['expires_in']
            ]);

            return redirect()->route('teamleader.index')
                ->with('success', 'Teamleader Focus connected successfully! You can now import data.');

        } catch (\Exception $e) {
            Log::error('Teamleader OAuth callback error', ['error' => $e->getMessage()]);
            return redirect()->route('teamleader.index')
                ->with('error', 'Authorization failed: ' . $e->getMessage());
        }
    }

    /**
     * Test API connection
     */
    public function testConnection()
    {
        // Authorization check - role-based
        if (Auth::user()->role !== 'super_admin') {
            abort(403, 'Access denied.');
        }

        try {
            $result = TeamleaderService::testConnection();

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'user' => $result['user']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Teamleader connection test failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Disconnect Teamleader (revoke tokens)
     */
    public function disconnect()
    {
        // Authorization check - role-based
        if (Auth::user()->role !== 'super_admin') {
            abort(403, 'Access denied.');
        }

        try {
            // Clear tokens from settings
            Setting::set('teamleader_access_token', '');
            Setting::set('teamleader_refresh_token', '');
            Setting::set('teamleader_token_expires_at', '');

            Log::info('Teamleader disconnected by user', ['user_id' => Auth::id()]);

            return redirect()->route('teamleader.index')
                ->with('success', 'Teamleader Focus disconnected successfully.');

        } catch (\Exception $e) {
            Log::error('Error disconnecting Teamleader', ['error' => $e->getMessage()]);
            return redirect()->route('teamleader.index')
                ->with('error', 'Failed to disconnect: ' . $e->getMessage());
        }
    }

    /**
     * Preview companies from Teamleader
     */
    public function previewCompanies()
    {
        // Authorization check - role-based
        if (Auth::user()->role !== 'super_admin') {
            abort(403, 'Access denied.');
        }

        try {
            $companies = TeamleaderService::listCompanies(1, 10);

            return response()->json([
                'success' => true,
                'data' => $companies
            ]);

        } catch (\Exception $e) {
            Log::error('Error previewing Teamleader companies', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview contacts from Teamleader
     */
    public function previewContacts()
    {
        // Authorization check - role-based
        if (Auth::user()->role !== 'super_admin') {
            abort(403, 'Access denied.');
        }

        try {
            $contacts = TeamleaderService::listContacts(1, 10);

            return response()->json([
                'success' => true,
                'data' => $contacts
            ]);

        } catch (\Exception $e) {
            Log::error('Error previewing Teamleader contacts', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview projects from Teamleader
     */
    public function previewProjects()
    {
        // Authorization check - role-based
        if (Auth::user()->role !== 'super_admin') {
            abort(403, 'Access denied.');
        }

        try {
            $projects = TeamleaderService::listProjects(1, 10);

            return response()->json([
                'success' => true,
                'data' => $projects
            ]);

        } catch (\Exception $e) {
            Log::error('Error previewing Teamleader projects', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show company selection page
     */
    public function selectCompanies()
    {
        // Authorization check - SUPER ADMIN ONLY
        if (Auth::user()->role !== 'super_admin') {
            abort(403, 'Access denied.');
        }

        try {
            // 🚀 NIEUWE METHODE: Haal companies uit DATABASE CACHE (sneller!)
            $allCompanies = DB::select("
                SELECT
                    tc.teamleader_id as id,
                    tc.name,
                    tc.vat_number,
                    tc.email,
                    tc.website,
                    COUNT(DISTINCT tp.teamleader_id) as project_count,
                    SUM(CASE WHEN tp.status = 'active' THEN 1 ELSE 0 END) as active_projects,
                    EXISTS(
                        SELECT 1 FROM customers c
                        WHERE c.teamleader_id = tc.teamleader_id
                    ) as is_imported
                FROM teamleader_companies tc
                LEFT JOIN teamleader_projects tp ON tp.teamleader_company_id = tc.teamleader_id
                GROUP BY tc.teamleader_id, tc.name, tc.vat_number, tc.email, tc.website
                ORDER BY
                    active_projects DESC,
                    project_count DESC,
                    tc.name ASC
            ");

            // Convert to array format voor view compatibility
            $allCompanies = array_map(function($company) {
                return [
                    'id' => $company->id,
                    'name' => $company->name ?? 'Unnamed',
                    'vat_number' => $company->vat_number ?? '-',
                    'email' => $company->email ?? '-',
                    'website' => $company->website ?? '-',
                    'project_count' => (int)$company->project_count,
                    'active_projects' => (int)$company->active_projects,
                    'is_imported' => (bool)$company->is_imported,
                    'status' => 'active' // Assume active, we don't have status in cache
                ];
            }, $allCompanies);

            return view('teamleader.select-companies', compact('allCompanies'));

        } catch (\Exception $e) {
            Log::error('Error fetching companies from cache', ['error' => $e->getMessage()]);
            return redirect()->route('teamleader.index')
                ->with('error', 'Failed to fetch companies: ' . $e->getMessage());
        }
    }

    /**
     * Import selected companies from Teamleader
     */
    public function importCompanies(Request $request)
    {
        // Authorization check - SUPER ADMIN ONLY
        if (Auth::user()->role !== 'super_admin') {
            abort(403, 'Access denied.');
        }

        // Check if specific companies are selected
        if ($request->has('company_ids')) {
            $validated = $request->validate([
                'company_ids' => 'required|array',
                'company_ids.*' => 'required|string',
                'import_contacts' => 'sometimes|boolean',
                'import_projects' => 'sometimes|boolean',
                'import_time_entries' => 'sometimes|boolean'
            ]);

            // Parse import options (1/0 strings to booleans)
            $options = [
                'import_contacts' => filter_var($request->input('import_contacts', false), FILTER_VALIDATE_BOOLEAN),
                'import_projects' => filter_var($request->input('import_projects', false), FILTER_VALIDATE_BOOLEAN),
                'import_time_entries' => filter_var($request->input('import_time_entries', false), FILTER_VALIDATE_BOOLEAN),
            ];

            // 🚀 NIEUW: Dispatch import job naar background queue
            try {
                ImportTeamleaderCustomer::dispatch(
                    $validated['company_ids'],
                    $options,
                    Auth::id()
                );

                Log::info('ImportTeamleaderCustomer job dispatched', [
                    'user_id' => Auth::id(),
                    'company_count' => count($validated['company_ids']),
                    'options' => $options
                ]);

                $companyCount = count($validated['company_ids']);
                $importTypes = [];
                if ($options['import_contacts']) $importTypes[] = 'contacts';
                if ($options['import_projects']) $importTypes[] = 'projects';
                if ($options['import_time_entries']) $importTypes[] = 'time entries';

                $message = "Import started in background for {$companyCount} " .
                          ($companyCount === 1 ? 'company' : 'companies');

                if (!empty($importTypes)) {
                    $message .= " (including " . implode(', ', $importTypes) . ")";
                }

                $message .= ". You will receive an email when the import is complete. " .
                          "You can continue working - the import runs independently!";

                return redirect()->route('teamleader.index')
                    ->with('success', $message);

            } catch (\Exception $e) {
                Log::error('Error dispatching ImportTeamleaderCustomer job', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return redirect()->route('teamleader.index')
                    ->with('error', 'Failed to start import: ' . $e->getMessage());
            }
        }

        // Import ALL companies - ook als background job
        try {
            // Haal alle company IDs op
            $allCompanies = TeamleaderService::listCompanies();
            $companyIds = array_column($allCompanies, 'id');

            if (empty($companyIds)) {
                return redirect()->route('teamleader.index')
                    ->with('error', 'No companies found in Teamleader to import.');
            }

            // Dispatch als background job
            ImportTeamleaderCustomer::dispatch(
                $companyIds,
                [
                    'import_contacts' => false,
                    'import_projects' => false,
                    'import_time_entries' => false
                ],
                Auth::id()
            );

            Log::info('ImportTeamleaderCustomer job dispatched (all companies)', [
                'user_id' => Auth::id(),
                'company_count' => count($companyIds)
            ]);

            return redirect()->route('teamleader.index')
                ->with('success', "Import started in background for ALL " . count($companyIds) .
                      " companies. You will receive an email when complete.");

        } catch (\Exception $e) {
            Log::error('Error importing all Teamleader companies', ['error' => $e->getMessage()]);
            return redirect()->route('teamleader.index')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Import projects from Teamleader
     */
    public function importProjects(Request $request)
    {
        // Authorization check - admin en hoger
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied.');
        }

        // Verhoog timeout voor project import (kan lang duren met milestones/tasks)
        set_time_limit(600); // 10 minuten
        ini_set('max_execution_time', 600);

        // Check if specific projects are selected
        if ($request->has('project_ids')) {
            $validated = $request->validate([
                'project_ids' => 'required|array',
                'project_ids.*' => 'required|string',
                'customer_id' => 'required|exists:customers,id'
            ]);

            try {
                $importService = new TeamleaderImportService();
                $result = $importService->importSelectedProjects($validated['project_ids'], $validated['customer_id']);

                $customer = \App\Models\Customer::find($validated['customer_id']);
                return redirect()->route('customers.show', $customer)
                    ->with('success', "Import completed! {$result['imported']} project(s) imported, {$result['skipped']} skipped.");

            } catch (\Exception $e) {
                Log::error('Error importing selected Teamleader projects', ['error' => $e->getMessage()]);
                $customer = \App\Models\Customer::find($validated['customer_id']);
                return redirect()->route('customers.show', $customer)
                    ->with('error', 'Import failed: ' . $e->getMessage());
            }
        }

        // Import ALL projects if no selection (alleen super_admin)
        if (Auth::user()->role !== 'super_admin') {
            abort(403, 'Access denied. Only super_admin can import all projects.');
        }

        try {
            $importService = new TeamleaderImportService();
            $result = $importService->importProjects();

            return redirect()->route('teamleader.index')
                ->with('success', "Import completed! {$result['imported']} projects imported, {$result['skipped']} skipped.");

        } catch (\Exception $e) {
            Log::error('Error importing Teamleader projects', ['error' => $e->getMessage()]);
            return redirect()->route('teamleader.index')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Import time tracking from Teamleader
     */
    public function importTimeTracking(Request $request)
    {
        // Authorization check - role-based
        if (Auth::user()->role !== 'super_admin') {
            abort(403, 'Access denied.');
        }

        try {
            $importService = new TeamleaderImportService();
            $result = $importService->importTimeTracking();

            return redirect()->route('teamleader.index')
                ->with('success', "Import completed! {$result['imported']} time entries imported, {$result['skipped']} skipped.");

        } catch (\Exception $e) {
            Log::error('Error importing Teamleader time tracking', ['error' => $e->getMessage()]);
            return redirect()->route('teamleader.index')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Show user selection page
     */
    public function selectUsers()
    {
        // Authorization check - SUPER ADMIN ONLY
        if (Auth::user()->role !== 'super_admin') {
            abort(403, 'Access denied.');
        }

        try {
            // Haal contacten uit database cache (NIET van API!)
            $cachedContacts = \App\Models\TeamleaderContact::whereNotNull('email')
                ->orderBy('full_name')
                ->get();

            // Transform naar array formaat voor view compatibility
            $allUsers = $cachedContacts->map(function($contact) {
                // Check has_company flag
                $companies = is_string($contact->companies) ? json_decode($contact->companies, true) : $contact->companies;
                $hasCompany = !empty($companies) && is_array($companies) && count($companies) > 0;

                // Check of al geïmporteerd (op email of teamleader_id)
                $isImported = \App\Models\User::where('email', $contact->email)
                    ->orWhere('teamleader_id', $contact->teamleader_id)
                    ->exists();

                return [
                    'id' => $contact->teamleader_id,
                    'name' => $contact->full_name ?? trim(($contact->first_name ?? '') . ' ' . ($contact->last_name ?? '')),
                    'email' => $contact->email,
                    'phone' => $contact->phone ?? $contact->mobile ?? '-',
                    'has_company' => $hasCompany,
                    'company_name' => $hasCompany && !empty($companies[0]) ? $companies[0] : '-',
                    'is_imported' => $isImported
                ];
            })->toArray();

            // Statistics
            $stats = [
                'total_contacts' => count($allUsers),
                'already_imported' => collect($allUsers)->where('is_imported', true)->count(),
                'available_to_import' => collect($allUsers)->where('is_imported', false)->count(),
                'with_companies' => collect($allUsers)->where('has_company', true)->count(),
                'standalone' => collect($allUsers)->where('has_company', false)->count(),
                'last_sync' => $cachedContacts->max('synced_at'),
            ];

            return view('teamleader.select-users', compact('allUsers', 'stats'));

        } catch (\Exception $e) {
            Log::error('Error fetching Teamleader contacts from database', ['error' => $e->getMessage()]);
            return redirect()->route('teamleader.index')
                ->with('error', 'Failed to fetch contacts: ' . $e->getMessage());
        }
    }

    /**
     * Import selected users from Teamleader
     */
    public function importUsers(Request $request)
    {
        // Authorization check - SUPER ADMIN ONLY
        if (Auth::user()->role !== 'super_admin') {
            abort(403, 'Access denied.');
        }

        // Verhoog timeout voor lange import operaties
        set_time_limit(600); // 10 minuten
        ini_set('max_execution_time', 600);

        // Check if specific users are selected
        if ($request->has('user_ids')) {
            $validated = $request->validate([
                'user_ids' => 'required|array',
                'user_ids.*' => 'required|string'
            ]);

            try {
                $importService = new TeamleaderImportService();
                $result = $importService->importSelectedUsers($validated['user_ids']);

                return redirect()->route('teamleader.index')
                    ->with('success', "Import completed! {$result['imported']} users imported, {$result['skipped']} skipped.");

            } catch (\Exception $e) {
                Log::error('Error importing selected Teamleader users', ['error' => $e->getMessage()]);
                return redirect()->route('teamleader.index')
                    ->with('error', 'Import failed: ' . $e->getMessage());
            }
        }

        // Import ALL users if no selection
        try {
            $importService = new TeamleaderImportService();
            $result = $importService->importUsers();

            return redirect()->route('teamleader.index')
                ->with('success', "Import completed! {$result['imported']} users imported, {$result['skipped']} skipped.");

        } catch (\Exception $e) {
            Log::error('Error importing Teamleader users', ['error' => $e->getMessage()]);
            return redirect()->route('teamleader.index')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Sync projects from Teamleader to local cache for a customer
     */
    public function syncProjectsForCustomer(Request $request)
    {
        // Authorization check - admin en hoger
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied.');
        }

        $customerId = $request->get('customer_id');
        if (!$customerId) {
            return redirect()->route('customers.index')
                ->with('error', 'Please select a customer first.');
        }

        $customer = Customer::findOrFail($customerId);

        if (!$customer->teamleader_id) {
            return redirect()->route('customers.show', $customer)
                ->with('error', 'This customer has no Teamleader ID.');
        }

        try {
            set_time_limit(300); // 5 minuten voor sync

            $syncService = new TeamleaderProjectSyncService();
            $stats = $syncService->syncProjectsForCustomer($customer, 10); // Max 10 pagina's = 1000 projecten

            $message = "Synced {$stats['synced']} new project(s), updated {$stats['updated']} existing project(s).";

            if ($stats['errors'] > 0) {
                $message .= " {$stats['errors']} error(s) occurred.";
            }

            return redirect()->route('teamleader.select.projects', ['customer_id' => $customer->id])
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Teamleader project sync failed', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('customers.show', $customer)
                ->with('error', 'Sync failed: ' . $e->getMessage());
        }
    }

    /**
     * Show project selection page (from local cache)
     */
    public function selectProjects(Request $request)
    {
        // Authorization check - admin en hoger
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied.');
        }

        // Customer ID is verplicht
        $customerId = $request->get('customer_id');
        if (!$customerId) {
            return redirect()->route('customers.index')
                ->with('error', 'Please select a customer first.');
        }

        $customer = Customer::findOrFail($customerId);

        if (!$customer->teamleader_id) {
            return redirect()->route('customers.show', $customer)
                ->with('error', 'This customer has no Teamleader ID. Please import the customer from Teamleader first.');
        }

        // Haal projecten uit database cache
        $query = TeamleaderProject::where('customer_id', $customer->id);

        // Filters toepassen
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Sorteer op datum (nieuwste eerst)
        $allProjects = $query->orderBy('starts_on', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Check of er cache data is
        $hasCachedProjects = $allProjects->count() > 0;
        $lastSyncDate = $allProjects->max('synced_at');

        // Haal ALLE projecten op voor deze customer (ongefiltered voor statistics)
        $allCustomerProjects = TeamleaderProject::where('customer_id', $customer->id)->get();

        // Statistics
        $stats = [
            'total_projects' => $allProjects->count(),
            'already_imported' => $allProjects->where('is_imported', true)->count(),
            'available_to_import' => $allProjects->where('is_imported', false)->count(),
            'last_sync' => $lastSyncDate,
            'needs_sync' => !$hasCachedProjects || ($lastSyncDate && $lastSyncDate->lt(now()->subHours(24))),
            // Status breakdown (ongefiltered)
            'by_status' => [
                'active' => $allCustomerProjects->where('status', 'active')->count(),
                'on_hold' => $allCustomerProjects->where('status', 'on_hold')->count(),
                'done' => $allCustomerProjects->where('status', 'done')->count(),
                'cancelled' => $allCustomerProjects->where('status', 'cancelled')->count(),
            ],
            'total_all_statuses' => $allCustomerProjects->count(),
        ];

        return view('teamleader.select-projects', compact('allProjects', 'stats', 'customer'));
    }

    /**
     * Import companies WITH their contacts (combined operation)
     */
    public function importCompaniesWithContacts(Request $request)
    {
        // Authorization check - SUPER ADMIN ONLY
        if (Auth::user()->role !== 'super_admin') {
            abort(403, 'Access denied.');
        }

        // Verhoog timeout voor zeer lange import operaties
        set_time_limit(1800); // 30 minuten voor complete import
        ini_set('max_execution_time', 1800);

        try {
            $importService = new TeamleaderImportService();

            Log::info('Starting combined companies + contacts import', [
                'user_id' => Auth::id(),
                'has_selection' => $request->has('company_ids')
            ]);

            // Check if specific companies are selected
            if ($request->has('company_ids')) {
                $validated = $request->validate([
                    'company_ids' => 'required|array',
                    'company_ids.*' => 'required|string'
                ]);

                $result = $importService->importCompaniesWithContacts($validated['company_ids']);
            } else {
                // Import ALLE companies met hun contacten
                $result = $importService->importCompaniesWithContacts();
            }

            // Build success message
            $message = "Import completed! " .
                "{$result['companies_imported']} companies imported ({$result['companies_skipped']} skipped), " .
                "{$result['contacts_imported']} contacts imported ({$result['contacts_skipped']} skipped).";

            if (!empty($result['errors'])) {
                $message .= " Errors occurred: " . implode(', ', array_slice($result['errors'], 0, 3));
                if (count($result['errors']) > 3) {
                    $message .= " ... and " . (count($result['errors']) - 3) . " more. Check logs for details.";
                }
            }

            Log::info('Combined import completed', $result);

            return redirect()->route('teamleader.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Fatal error in combined import', ['error' => $e->getMessage()]);
            return redirect()->route('teamleader.index')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Global sync - Sync ALLE data uit Teamleader naar database
     */
    public function syncAll(Request $request)
    {
        // Authorization check - SUPER ADMIN ONLY
        if (Auth::user()->role !== 'super_admin') {
            abort(403, 'Access denied. Only super admin can perform global sync.');
        }

        // Verhoog timeout voor zeer lange operatie (45 minuten!)
        set_time_limit(2700); // 45 minuten
        ini_set('max_execution_time', 2700);

        try {
            $syncService = new TeamleaderProjectSyncService();

            Log::info('Starting GLOBAL sync of all Teamleader data', [
                'user_id' => Auth::id(),
                'timestamp' => now()
            ]);

            // Sync ALLE data uit Teamleader
            Log::info('Step 1/3: Syncing companies...');
            $companyStats = $syncService->syncAllCompaniesFromTeamleader(50); // Max 50 pages = 5,000 companies

            Log::info('Step 2/3: Syncing contacts...');
            $contactStats = $syncService->syncAllContactsFromTeamleader(50); // Max 50 pages = 5,000 contacts

            Log::info('Step 3/3: Syncing projects...');
            $projectStats = $syncService->syncAllProjectsFromTeamleader(100); // Max 100 pages = 10,000 projects

            // DISABLED: Milestones & Tasks sync - created during project import
            // Log::info('Step 4/5: Syncing milestones...');
            // $milestoneStats = $syncService->syncAllMilestonesFromTeamleader(100);

            // Log::info('Step 5/5: Syncing tasks...');
            // $taskStats = $syncService->syncAllTasksFromTeamleader(100);

            // DISABLED: Time Tracking sync - will be uploaded separately
            // Log::info('Step 6/6: Syncing time entries...');
            // $timeEntryStats = $syncService->syncAllTimeEntriesFromTeamleader(100);

            // TODO: Add Invoices sync

            // Build comprehensive success message
            $message = "🌍 GLOBAL SYNC COMPLETED!\n\n" .
                "🏢 Companies: {$companyStats['synced']} new, {$companyStats['updated']} updated " .
                "(Total: {$companyStats['total_fetched']} companies)\n\n" .
                "👥 Contacts: {$contactStats['synced']} new, {$contactStats['updated']} updated " .
                "(Total: {$contactStats['total_fetched']} contacts)\n\n" .
                "📊 Projects: {$projectStats['synced']} new, {$projectStats['updated']} updated, " .
                "{$projectStats['no_customer_found']} without customer match " .
                "(Total: {$projectStats['total_fetched']} projects)\n\n" .
                "ℹ️ Milestones & Tasks: Will be created during project import\n" .
                "⏱️ Time Entries: Will be uploaded separately\n\n" .
                "✅ All Teamleader data has been synchronized to Progress database!";

            $totalErrors = $companyStats['errors'] + $contactStats['errors'] + $projectStats['errors'];
            if ($totalErrors > 0) {
                $message .= "\n\n⚠️ Total errors: {$totalErrors} (check logs for details)";
            }

            return redirect()->route('teamleader.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('GLOBAL sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('teamleader.index')
                ->with('error', 'Global sync failed: ' . $e->getMessage());
        }
    }

    /**
     * Sync contact-company relationships (REVERSE SYNC)
     */
    public function syncContactCompanyRelationships(Request $request)
    {
        // Authorization check - SUPER ADMIN ONLY
        if (Auth::user()->role !== 'super_admin') {
            abort(403, 'Access denied. Only super admin can perform reverse sync.');
        }

        // Verhoog timeout voor lange operatie
        set_time_limit(1800); // 30 minuten
        ini_set('max_execution_time', 1800);

        try {
            // Maak sync job aan voor progress tracking
            $syncJob = \App\Models\SyncJob::create([
                'job_type' => 'contact_companies',
                'status' => 'running',
                'user_id' => Auth::id(),
                'total_items' => 0, // Wordt later bijgewerkt
                'started_at' => now(),
            ]);

            Log::info('Starting REVERSE sync of contact-company relationships in BACKGROUND', [
                'user_id' => Auth::id(),
                'sync_job_id' => $syncJob->id,
                'timestamp' => now()
            ]);

            // Start sync in BACKGROUND process (voorkomt gateway timeout)
            $command = sprintf(
                'cd %s && php artisan teamleader:sync-contact-companies %d > /dev/null 2>&1 &',
                base_path(),
                $syncJob->id
            );

            exec($command);

            // Redirect DIRECT terug (sync draait in background)
            return redirect()->route('teamleader.index')
                ->with('success', '🔄 Sync started in background! Progress will be tracked on this page.');

        } catch (\Exception $e) {
            Log::error('Failed to start REVERSE sync', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('teamleader.index')
                ->with('error', 'Failed to start sync: ' . $e->getMessage());
        }
    }

    /**
     * Get sync status (AJAX endpoint voor progress tracking)
     */
    public function getSyncStatus(Request $request)
    {
        $jobType = $request->get('job_type', 'contact_companies');

        // Haal de laatste running of completed sync op van dit type
        $syncJob = \App\Models\SyncJob::where('job_type', $jobType)
            ->whereIn('status', ['running', 'completed'])
            ->latest('created_at')
            ->first();

        if (!$syncJob) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'No active sync job found'
            ]);
        }

        return response()->json([
            'status' => $syncJob->status,
            'progress_percentage' => $syncJob->progress_percentage,
            'total_items' => $syncJob->total_items,
            'processed_items' => $syncJob->processed_items,
            'successful_items' => $syncJob->successful_items,
            'failed_items' => $syncJob->failed_items,
            'current_item' => $syncJob->current_item,
            'started_at' => $syncJob->started_at?->format('Y-m-d H:i:s'),
            'completed_at' => $syncJob->completed_at?->format('Y-m-d H:i:s'),
            'error_message' => $syncJob->error_message,
        ]);
    }

    /**
     * Show contact selection page for a specific customer
     */
    public function selectContacts(Request $request)
    {
        // Authorization check - admin en hoger
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied.');
        }

        // Customer ID is verplicht
        $customerId = $request->get('customer_id');
        if (!$customerId) {
            return redirect()->route('customers.index')
                ->with('error', 'Please select a customer first.');
        }

        $customer = Customer::findOrFail($customerId);

        if (!$customer->teamleader_id) {
            return redirect()->route('customers.show', $customer)
                ->with('error', 'This customer has no Teamleader ID.');
        }

        try {
            // STAP 1: Probeer eerst filtering via DATABASE CACHE (snel!)
            // We zoeken in raw_data['companies'] array naar de customer's teamleader_id

            $allTeamleaderContacts = \App\Models\TeamleaderContact::whereNotNull('email')
                ->orderBy('full_name')
                ->get();

            $filteredContacts = $allTeamleaderContacts->filter(function($tlContact) use ($customer) {
                $rawData = $tlContact->raw_data;

                // Check raw_data['companies'] array
                if ($rawData && isset($rawData['companies']) && is_array($rawData['companies'])) {
                    foreach ($rawData['companies'] as $company) {
                        $companyId = is_array($company) && isset($company['company']['id'])
                            ? $company['company']['id']
                            : null;

                        if ($companyId === $customer->teamleader_id) {
                            return true; // Match gevonden!
                        }
                    }
                }

                return false;
            });

            Log::info('Filtered contacts via database cache', [
                'customer_id' => $customerId,
                'customer_name' => $customer->name,
                'teamleader_id' => $customer->teamleader_id,
                'total_cached' => $allTeamleaderContacts->count(),
                'filtered_count' => $filteredContacts->count()
            ]);

            // STAP 2: Als cache filtering weinig resultaten geeft, gebruik API als fallback
            if ($filteredContacts->count() < 3) {
                Log::info('Cache filtering gave few results, using API fallback', [
                    'cache_results' => $filteredContacts->count()
                ]);

                // Gebruik API met CORRECTE filter syntax
                $apiContacts = [];
                $page = 1;

                do {
                    $response = TeamleaderService::listContactsForCompany(
                        $customer->teamleader_id,
                        $page,
                        100
                    );

                    $pageContacts = $response['data'] ?? [];
                    $apiContacts = array_merge($apiContacts, $pageContacts);
                    $page++;

                } while (count($pageContacts) === 100 && $page <= 10); // Max 10 pagina's

                Log::info('API filtering completed', [
                    'api_results' => count($apiContacts)
                ]);

                // Converteer API resultaten naar TeamleaderContact-achtige objecten
                $filteredContacts = collect($apiContacts)->map(function($contact) {
                    return (object)[
                        'teamleader_id' => $contact['id'],
                        'full_name' => trim(($contact['first_name'] ?? '') . ' ' . ($contact['last_name'] ?? '')),
                        'email' => $contact['emails'][0]['email'] ?? null,
                        'phone' => $contact['telephones'][0]['number'] ?? null,
                        'mobile' => null,
                        'position' => null,
                    ];
                });
            }

            // Converteer naar array voor de view
            $allContacts = [];
            foreach ($filteredContacts as $tlContact) {
                // Check of al geïmporteerd voor DEZE customer
                $isImported = \App\Models\Contact::where('customer_id', $customerId)
                    ->where(function($q) use ($tlContact) {
                        $q->where('email', $tlContact->email)
                          ->orWhere('teamleader_id', $tlContact->teamleader_id);
                    })
                    ->exists();

                $allContacts[] = [
                    'id' => $tlContact->teamleader_id,
                    'name' => $tlContact->full_name ?? trim(($tlContact->first_name ?? '') . ' ' . ($tlContact->last_name ?? '')),
                    'email' => $tlContact->email ?? '',
                    'phone' => $tlContact->phone ?? $tlContact->mobile ?? '-',
                    'position' => $tlContact->position ?? '-',
                    'is_imported' => $isImported
                ];
            }

            // Statistics
            $stats = [
                'total_contacts' => count($allContacts),
                'already_imported' => collect($allContacts)->where('is_imported', true)->count(),
                'available_to_import' => collect($allContacts)->where('is_imported', false)->count(),
                'last_sync' => $allTeamleaderContacts->max('synced_at'),
            ];

            return view('teamleader.select-contacts', compact('allContacts', 'stats', 'customer'));

        } catch (\Exception $e) {
            Log::error('Error fetching Teamleader contacts', [
                'customer_id' => $customerId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('customers.show', $customer)
                ->with('error', 'Failed to fetch contacts: ' . $e->getMessage());
        }
    }

    /**
     * Import selected contacts for a customer
     */
    public function importContacts(Request $request)
    {
        // Authorization check - admin en hoger
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied.');
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'contact_ids' => 'required|array|min:1',
            'contact_ids.*' => 'required|string'
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);

        try {
            $imported = 0;
            $skipped = 0;

            DB::beginTransaction();

            foreach ($validated['contact_ids'] as $contactId) {
                // Haal contact op uit database cache
                $tlContact = \App\Models\TeamleaderContact::where('teamleader_id', $contactId)->first();

                if (!$tlContact) {
                    $skipped++;
                    continue;
                }

                // Check of al bestaat voor deze customer
                $existingContact = \App\Models\Contact::where('customer_id', $customer->id)
                    ->where(function($q) use ($tlContact) {
                        $q->where('email', $tlContact->email)
                          ->orWhere('teamleader_id', $tlContact->teamleader_id);
                    })
                    ->first();

                if ($existingContact) {
                    $skipped++;
                    continue;
                }

                // Haal position op uit Teamleader API (contacts.info bevat companies array met position)
                $position = null;
                try {
                    $contactInfo = TeamleaderService::getContact($contactId);
                    $contactData = $contactInfo['data'] ?? $contactInfo;

                    // Zoek de position voor deze specifieke company
                    if (isset($contactData['companies']) && is_array($contactData['companies'])) {
                        foreach ($contactData['companies'] as $companyRelation) {
                            $companyId = $companyRelation['company']['id'] ?? null;

                            if ($companyId === $customer->teamleader_id) {
                                $position = $companyRelation['position'] ?? null;
                                break;
                            }
                        }

                        // Als geen match voor deze company, neem de eerste position
                        if (!$position && !empty($contactData['companies'])) {
                            $position = $contactData['companies'][0]['position'] ?? null;
                        }
                    }

                    Log::info('Fetched position from Teamleader API', [
                        'contact_id' => $contactId,
                        'contact_name' => $tlContact->full_name,
                        'position' => $position,
                        'customer_teamleader_id' => $customer->teamleader_id
                    ]);

                } catch (\Exception $e) {
                    Log::warning('Failed to fetch position from API', [
                        'contact_id' => $contactId,
                        'error' => $e->getMessage()
                    ]);
                    // Continue met import zonder position
                }

                // Maak contact aan voor deze customer
                \App\Models\Contact::create([
                    'customer_id' => $customer->id,
                    'company_id' => Auth::user()->company_id,
                    'teamleader_id' => $tlContact->teamleader_id,
                    'name' => $tlContact->full_name ?? trim(($tlContact->first_name ?? '') . ' ' . ($tlContact->last_name ?? '')),
                    'email' => $tlContact->email,
                    'phone' => $tlContact->phone ?? $tlContact->mobile,
                    'position' => $position, // Nu uit API gehaald!
                    'is_primary' => false, // Handmatig instellen later
                    'is_active' => true,
                ]);

                $imported++;
            }

            DB::commit();

            return redirect()->route('customers.show', $customer)
                ->with('success', "Import completed! {$imported} contact(s) imported, {$skipped} skipped.");

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error importing contacts for customer', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage()
            ]);
            return redirect()->route('customers.show', $customer)
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
