<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Check of companies plugin actief is
     */
    private function isCompaniesPluginActive(): bool
    {
        // Companies functionality is always active
        return true;
    }
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        // Authorization check - alleen super_admin en admin
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can manage users.');
        }

        $user = Auth::user();

        // Query building met company eager loading
        $query = User::with(['company']);

        // Verberg super_admin users voor iedereen behalve super_admin zelf
        if ($user->role !== 'super_admin') {
            $query->where('role', '!=', 'super_admin');
        }

        // Super admin en admin zien alle users (behalve super_admin)

        // Search filtering
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('first_name', 'like', '%' . $search . '%')
                  ->orWhere('last_name', 'like', '%' . $search . '%');
            });
        }

        // Status filtering
        if ($request->filled('status')) {
            $active = $request->status === 'active' ? 1 : 0;
            $query->where('is_active', $active);
        }

        // Role filtering
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Company filtering (voor super_admin en admin)
        if ($request->filled('company_id') && in_array($user->role, ['super_admin', 'admin'])) {
            $query->where('company_id', $request->company_id);
        }

        $users = $query->orderBy('name')->paginate(20)->withQueryString();

        // Get companies voor filter dropdown (voor super_admin en admin)
        $companies = collect();
        if (in_array($user->role, ['super_admin', 'admin']) && $this->isCompaniesPluginActive()) {
            $companies = Company::where('is_active', true)->orderBy('name')->get();
        }

        // Calculate stats (exclude super_admin users from stats for non-super_admin)
        $stats = [
            'total_users' => User::when($user->role !== 'super_admin', function($q) {
                $q->where('role', '!=', 'super_admin');
            })->count(),
            'active_users' => User::when($user->role !== 'super_admin', function($q) {
                $q->where('role', '!=', 'super_admin');
            })->where('is_active', true)->count(),
            'inactive_users' => User::when($user->role !== 'super_admin', function($q) {
                $q->where('role', '!=', 'super_admin');
            })->where('is_active', false)->count(),
        ];

        $pageTitle = 'Users';
        $pageDescription = 'Manage all users in your organization';

        $isCompaniesPluginActive = $this->isCompaniesPluginActive();
        return view('users.index', compact('users', 'companies', 'stats', 'pageTitle', 'pageDescription', 'isCompaniesPluginActive'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        // Authorization check
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can create users.');
        }

        $user = Auth::user();

        // Get companies voor dropdown (alleen als plugin actief)
        $companies = collect();
        if ($this->isCompaniesPluginActive()) {
            if (in_array($user->role, ['super_admin', 'admin'])) {
                // Super admin en admin kunnen alle companies zien
                $companies = Company::where('is_active', true)->orderBy('name')->get();
            }
        }

        // Roles die deze gebruiker mag toewijzen
        $availableRoles = $this->getAvailableRoles($user);

        $pageTitle = 'New User';
        $pageDescription = 'Add a new user to the system';

        return view('users.create', compact('companies', 'availableRoles', 'pageTitle', 'pageDescription'));
    }

    /**
     * Store a newly created user in storage
     */
    public function store(Request $request)
    {
        // Authorization check
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can create users.');
        }

        $user = Auth::user();

        // Validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'phone' => 'nullable|string|max:50',
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => ['required', Rule::in(array_keys($this->getAvailableRoles($user)))],
            'is_active' => 'boolean',
        ];

        // Company validation (super_admin en admin)
        if (in_array($user->role, ['super_admin', 'admin'])) {
            $rules['company_id'] = 'required|exists:companies,id';
        }

        $request->validate($rules);

        try {
            DB::beginTransaction();

            // Data preparation
            $data = [
                'name' => $request->name,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'is_active' => $request->boolean('is_active'),
                'auto_approve_time_entries' => $request->boolean('auto_approve_time_entries'),
                'company_id' => in_array($user->role, ['super_admin', 'admin']) ? $request->company_id : $user->company_id,
                // Verwijder auto-verificatie, laat gebruiker zelf email verifiëren
                'email_verified_at' => null,
            ];

            // Create user
            $newUser = User::create($data);

            // Verstuur verificatie email
            try {
                $newUser->sendEmailVerificationNotification();
                $emailMessage = ' A verification email has been sent to ' . $newUser->email;
            } catch (\Exception $e) {
                // Als email verzenden mislukt, log de error maar laat de user wel aanmaken
                \Log::error('Failed to send verification email to user: ' . $newUser->email . '. Error: ' . $e->getMessage());
                $emailMessage = ' (Note: Verification email could not be sent. Please check email configuration.)';
            }

            DB::commit();

            return redirect()->route('users.index')
                ->with('success', 'User created successfully.' . $emailMessage);

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Error creating user: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        // Authorization check
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $currentUser = Auth::user();

        // Check if user can view this user
        if (!$this->canViewUser($currentUser, $user)) {
            abort(403, 'Access denied. You cannot view this user.');
        }

        // Load relationships
        $user->load(['company', 'projects']);

        // Get user's project stats
        $stats = [
            'total_projects' => $user->projects->count(),
            'active_projects' => $user->projects->where('status', 'active')->count(),
            'total_hours' => $user->timeEntries()->sum('hours') ?? 0,
            'pending_hours' => $user->timeEntries()->where('status', 'pending')->sum('hours') ?? 0,
        ];

        $pageTitle = $user->name;
        $pageDescription = 'User details and activity overview';

        return view('users.show', compact('user', 'stats', 'pageTitle', 'pageDescription'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        // Authorization check
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $currentUser = Auth::user();

        // Check if user can edit this user
        if (!$this->canEditUser($currentUser, $user)) {
            abort(403, 'Access denied. You cannot edit this user.');
        }

        // Get companies voor dropdown (alleen als plugin actief)
        $companies = collect();
        if ($this->isCompaniesPluginActive()) {
            if (in_array($currentUser->role, ['super_admin', 'admin'])) {
                // Super admin en admin kunnen alle companies zien
                $companies = Company::where('is_active', true)->orderBy('name')->get();
            }
        }

        // Roles die deze gebruiker mag toewijzen
        $availableRoles = $this->getAvailableRoles($currentUser);

        $pageTitle = 'Edit User';
        $pageDescription = 'Edit user: ' . $user->name;

        return view('users.edit', compact('user', 'companies', 'availableRoles', 'pageTitle', 'pageDescription'));
    }

    /**
     * Return show details HTML for modal
     */
    public function showModal(User $user)
    {
        // Authorization check
        if (!Auth::check()) {
            abort(401);
        }

        $currentUser = Auth::user();

        // Check if user can view this user
        if (!$this->canViewUser($currentUser, $user)) {
            abort(403, 'Access denied. You cannot view this user.');
        }

        // Load relationships
        $user->load(['company']);

        // Return modal view
        return view('users.show-modal', compact('user'));
    }

    /**
     * Return edit form HTML for modal
     */
    public function editModal(User $user)
    {
        // Authorization check
        if (!Auth::check()) {
            abort(401);
        }

        $currentUser = Auth::user();

        // Check if user can edit this user
        if (!$this->canEditUser($currentUser, $user)) {
            abort(403, 'Access denied. You cannot edit this user.');
        }

        // Load relationships
        $user->load(['company']);

        // Companies (voor super_admin en admin)
        $companies = null;
        if (in_array($currentUser->role, ['super_admin', 'admin']) && $this->isCompaniesPluginActive()) {
            $companies = Company::where('is_active', true)->orderBy('name')->get();
        }

        // Return modal form view
        return view('users.edit-modal', compact('user', 'companies'));
    }

    /**
     * Update the specified user in storage
     */
    public function update(Request $request, User $user)
    {
        // Authorization check
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $currentUser = Auth::user();

        // Check if user can edit this user
        if (!$this->canEditUser($currentUser, $user)) {
            abort(403, 'Access denied. You cannot edit this user.');
        }

        // Validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:50',
            'role' => ['required', Rule::in(array_keys($this->getAvailableRoles($currentUser)))],
            'is_active' => 'boolean',
        ];

        // Password is optioneel bij update
        if ($request->filled('password')) {
            $rules['password'] = ['confirmed', Password::min(8)];
        }

        // Company validation (super_admin en admin mogen company wijzigen)
        if (in_array($currentUser->role, ['super_admin', 'admin'])) {
            $rules['company_id'] = 'required|exists:companies,id';
        }

        $request->validate($rules);

        try {
            DB::beginTransaction();

            // Data preparation
            $data = [
                'name' => $request->name,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'role' => $request->role,
                'is_active' => $request->boolean('is_active'),
                'auto_approve_time_entries' => $request->boolean('auto_approve_time_entries'),
            ];

            // Update password indien opgegeven
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            // Update company voor super_admin en admin
            if (in_array($currentUser->role, ['super_admin', 'admin'])) {
                $data['company_id'] = $request->company_id;
            }

            // Update user
            $user->update($data);

            DB::commit();

            // Check if this is an AJAX request (from modal)
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User updated successfully'
                ]);
            }

            return redirect()->route('users.show', $user)
                ->with('success', 'User updated successfully');

        } catch (\Exception $e) {
            DB::rollback();

            // Check if this is an AJAX request (from modal)
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating user: ' . $e->getMessage()
                ], 500);
            }

            return back()->withInput()
                ->with('error', 'Error updating user: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified user from storage (soft delete)
     */
    public function destroy(User $user)
    {
        // Authorization check
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $currentUser = Auth::user();

        // Check if user can delete this user
        if (!$this->canDeleteUser($currentUser, $user)) {
            abort(403, 'Access denied. You cannot delete this user.');
        }

        // Prevent self-deletion
        if ($user->id === $currentUser->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        // NOTE: We don't check for active projects or pending time entries here
        // because soft delete is reversible. The user is deactivated and can be restored.
        // For permanent deletion (forceDelete), we DO check these relationships.

        try {
            DB::beginTransaction();

            // First deactivate the user
            $user->update(['is_active' => false]);

            // Then soft delete
            $user->delete();
            
            DB::commit();

            return redirect()->route('users.index')
                ->with('success', 'User deleted successfully. The user can be restored if needed.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error deleting user: ' . $e->getMessage());
        }
    }

    /**
     * Bulk update users
     */
    public function bulkAction(Request $request)
    {
        // Authorization check
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can perform bulk actions.');
        }

        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        try {
            DB::beginTransaction();

            $currentUser = Auth::user();
            $users = User::whereIn('id', $request->user_ids);

            // Admin kan alleen users van eigen company updaten
            if ($currentUser->role === 'admin') {
                $users->where('company_id', $currentUser->company_id);
            }

            // Prevent self-deactivation
            if ($request->action === 'deactivate') {
                $users->where('id', '!=', $currentUser->id);
            }

            $count = $users->count();

            switch ($request->action) {
                case 'activate':
                    $users->update(['is_active' => true]);
                    $message = "{$count} users activated successfully";
                    break;
                case 'deactivate':
                    $users->update(['is_active' => false]);
                    $message = "{$count} users deactivated successfully";
                    break;
                case 'delete':
                    // First deactivate, then soft delete
                    $users->update(['is_active' => false]);

                    // Get the users collection and soft delete each one
                    $userCollection = $users->get();
                    foreach ($userCollection as $user) {
                        $user->delete();
                    }
                    $message = "{$count} users deleted successfully";
                    break;
            }

            DB::commit();

            // Return JSON for AJAX requests
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();

            // Return JSON error for AJAX requests
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error performing bulk action: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Error performing bulk action: ' . $e->getMessage());
        }
    }

    /**
     * Show deleted users
     */
    public function deleted(Request $request)
    {
        // Authorization check - alleen super_admin en admin
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can view deleted users.');
        }

        $user = Auth::user();
        
        // Query building voor deleted users
        $query = User::onlyTrashed()->with(['company']);

        // Super admin ziet alle deleted users, admin alleen van eigen company
        if ($user->role === 'admin') {
            $query->where('company_id', $user->company_id);
        }

        // Search filtering
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        $deletedUsers = $query->orderBy('deleted_at', 'desc')->paginate(20)->withQueryString();

        $pageTitle = 'Deleted Users';
        $pageDescription = 'View and restore deleted users';

        return view('users.deleted', compact('deletedUsers', 'pageTitle', 'pageDescription'));
    }

    /**
     * Restore a soft deleted user
     */
    public function restore($id)
    {
        // Authorization check
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can restore users.');
        }

        $user = User::onlyTrashed()->findOrFail($id);

        // Check if current user can restore this user
        if (Auth::user()->role === 'admin' && $user->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only restore users from your own company.');
        }

        try {
            DB::beginTransaction();

            // Restore the user
            $user->restore();
            
            // Reactivate the user
            $user->update(['is_active' => true]);
            
            DB::commit();

            return redirect()->route('users.index')
                ->with('success', 'User restored successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error restoring user: ' . $e->getMessage());
        }
    }

    /**
     * Permanently delete a soft-deleted user (force delete)
     */
    public function forceDelete($id)
    {
        // Authorization check
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can permanently delete users.');
        }

        // Get the soft-deleted user
        $user = User::onlyTrashed()->findOrFail($id);

        // Check if current user can delete this user (company isolation)
        if (Auth::user()->role === 'admin' && $user->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only permanently delete users from your own company.');
        }

        // Prevent force deleting yourself (extra safety check, though this shouldn't happen)
        if ($user->id === Auth::user()->id) {
            return back()->with('error', 'You cannot permanently delete your own account.');
        }

        // BELANGRIJK: Check if user has important relationships before PERMANENT deletion
        // Permanent deletion kan data orphanen, dus we controleren op belangrijke relaties
        $hasAnyProjects = $user->projects()->exists();
        $hasAnyTimeEntries = $user->timeEntries()->exists();

        if ($hasAnyProjects || $hasAnyTimeEntries) {
            return back()->with('error', 'Cannot permanently delete user with projects or time entries. This would orphan important data. Please keep the user in the soft-deleted state.');
        }

        try {
            DB::beginTransaction();

            // Store user info for success message
            $userName = $user->name;
            $userEmail = $user->email;

            // Permanently delete the user from database
            // This will CASCADE delete all relationships that have ON DELETE CASCADE
            $user->forceDelete();

            DB::commit();

            return redirect()->route('users.deleted')
                ->with('success', "User '{$userName}' ({$userEmail}) has been permanently deleted from the database. This action cannot be undone.");

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error permanently deleting user', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Error permanently deleting user: ' . $e->getMessage());
        }
    }

    /**
     * Resend email verification notification
     */
    public function resendVerification(User $user)
    {
        // Authorization check
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $currentUser = Auth::user();

        // Check if user can manage this user
        if (!$this->canEditUser($currentUser, $user)) {
            abort(403, 'Access denied. You cannot manage this user.');
        }

        // Check if user is already verified
        if ($user->hasVerifiedEmail()) {
            return back()->with('info', 'User email is already verified.');
        }

        try {
            // Send verification email
            $user->sendEmailVerificationNotification();
            
            // Voor super_admin, toon ook de verificatie URL voor debugging
            $message = 'Verification email sent successfully to ' . $user->email;
            if ($currentUser->role === 'super_admin') {
                $verificationUrl = \URL::temporarySignedRoute(
                    'verification.verify',
                    now()->addMinutes(60),
                    [
                        'id' => $user->getKey(),
                        'hash' => sha1($user->getEmailForVerification()),
                    ]
                );
                $message .= '<br><br><strong>Manual verification link (for debugging):</strong><br><a href="' . $verificationUrl . '" target="_blank" class="text-blue-600 underline">' . $verificationUrl . '</a>';
            }
            
            return back()->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Failed to send verification email to user: ' . $user->email . '. Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to send verification email. Please check email configuration.');
        }
    }

    /**
     * Helper: Get available roles based on current user's role
     */
    private function getAvailableRoles($user)
    {
        if ($user->role === 'super_admin') {
            return [
                'super_admin' => 'Super Administrator',
                'admin' => 'Company Administrator',
                'project_manager' => 'Project Manager',
                'user' => 'Regular User',
                'reader' => 'Read-only User',
            ];
        } elseif ($user->role === 'admin') {
            // Admin kan geen super_admin aanmaken
            return [
                'admin' => 'Company Administrator',
                'project_manager' => 'Project Manager',
                'user' => 'Regular User',
                'reader' => 'Read-only User',
            ];
        }

        return [];
    }

    /**
     * Helper: Check if current user can view target user
     */
    private function canViewUser($currentUser, $targetUser)
    {
        // Super admin kan iedereen zien
        if ($currentUser->role === 'super_admin') {
            return true;
        }

        // Admin kan alleen users van eigen company zien
        if ($currentUser->role === 'admin') {
            return $targetUser->company_id === $currentUser->company_id;
        }

        // Anderen kunnen alleen zichzelf zien
        return $currentUser->id === $targetUser->id;
    }

    /**
     * Helper: Check if current user can edit target user
     */
    private function canEditUser($currentUser, $targetUser)
    {
        // Super admin kan iedereen bewerken
        if ($currentUser->role === 'super_admin') {
            return true;
        }

        // Admin kan users van eigen company bewerken, maar geen super_admin
        if ($currentUser->role === 'admin') {
            return $targetUser->company_id === $currentUser->company_id && 
                   $targetUser->role !== 'super_admin';
        }

        return false;
    }

    /**
     * Helper: Check if current user can delete target user
     */
    private function canDeleteUser($currentUser, $targetUser)
    {
        // Niemand kan zichzelf verwijderen
        if ($currentUser->id === $targetUser->id) {
            return false;
        }

        return $this->canEditUser($currentUser, $targetUser);
    }
}