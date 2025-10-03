<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectMilestoneController;
use App\Http\Controllers\ProjectTaskController;
use App\Http\Controllers\ProjectSubtaskController;
use App\Http\Controllers\TimeEntryController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoiceDashboardController;
use App\Http\Controllers\InvoiceTemplateController;
use App\Http\Controllers\InvoiceAITestController;
use App\Http\Controllers\AiLearningController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ServiceCategoryController;
use App\Http\Controllers\ServiceMilestoneController;
use App\Http\Controllers\ServiceTaskController;
use App\Http\Controllers\ServiceSubtaskController;
use App\Http\Controllers\ProjectTemplateController;
use App\Http\Controllers\ProjectAdditionalCostController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\MsGraphAuthController;
use App\Http\Controllers\QuickReportsController;
use App\Http\Controllers\ProjectIntelligenceController;
use App\Http\Controllers\AIChatController;
use App\Http\Controllers\AIDigestController;
use App\Http\Controllers\ProjectMediaCampaignController;

// Public routes
Route::get('/', function () {
    return redirect('/dashboard');
});

// Logo serving route (public access for previews)
Route::get('template-logo/{path}', function($path) {
    $fullPath = storage_path('app/public/logos/' . $path);

    if (!file_exists($fullPath)) {
        abort(404);
    }

    $mimeType = mime_content_type($fullPath);
    return response()->file($fullPath, ['Content-Type' => $mimeType]);
})->where('path', '.*')->name('template.logo');

// Authentication Routes (Laravel Breeze)
require __DIR__.'/auth.php';

// Protected routes
Route::middleware(['auth'])->group(function () {
    // =====================================
    // DASHBOARD
    // =====================================
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // =====================================
    // COMPANY MANAGEMENT
    // =====================================

    // Company Settings - Altijd beschikbaar voor single company mode
    Route::get('company-settings', [CompanyController::class, 'settings'])->name('company.settings');
    Route::put('company-settings', [CompanyController::class, 'updateSettings'])->name('company.settings.update');

    // Multi-Company Management
    Route::get('companies/{company}/show-modal', [CompanyController::class, 'showModal'])->name('companies.show-modal');
    Route::get('companies/{company}/edit-modal', [CompanyController::class, 'editModal'])->name('companies.edit-modal');
    Route::resource('companies', CompanyController::class);
    Route::post('companies/bulk-update', [CompanyController::class, 'bulkUpdate'])->name('companies.bulk-update');
    Route::get('companies/export', [CompanyController::class, 'export'])->name('companies.export');
    Route::get('companies/{company}/activity', [CompanyController::class, 'activity'])->name('companies.activity');

    // =====================================
    // CUSTOMER MANAGEMENT
    // =====================================
    Route::resource('customers', CustomerController::class);
    Route::post('customers/bulk-update', [CustomerController::class, 'bulkUpdate'])->name('customers.bulk-update');
    Route::get('customers/export', [CustomerController::class, 'export'])->name('customers.export');
    Route::patch('customers/{customer}/update-inline', [CustomerController::class, 'updateInline'])->name('customers.update-inline');

    // =====================================
    // CONTACT MANAGEMENT
    // =====================================
    Route::resource('contacts', ContactController::class);
    Route::post('contacts/{contact}/toggle-primary', [ContactController::class, 'togglePrimary'])->name('contacts.togglePrimary');

    // =====================================
    // USER MANAGEMENT
    // =====================================
    Route::get('users/{user}/show-modal', [UserController::class, 'showModal'])->name('users.show-modal');
    Route::get('users/{user}/edit-modal', [UserController::class, 'editModal'])->name('users.edit-modal');
    Route::get('users/deleted', [UserController::class, 'deleted'])->name('users.deleted');
    Route::post('users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');
    Route::resource('users', UserController::class);
    Route::post('users/bulk-action', [UserController::class, 'bulkAction'])->name('users.bulk-action');
    Route::post('users/{user}/resend-verification', [UserController::class, 'resendVerification'])->name('users.resend-verification');

    // =====================================
    // PROJECT MANAGEMENT 🚀 UPDATED
    // =====================================
    Route::resource('projects', ProjectController::class);
    Route::post('projects/{project}/duplicate', [ProjectController::class, 'duplicate'])->name('projects.duplicate');
    Route::get('projects/{project}/export', [ProjectController::class, 'export'])->name('projects.export');
    Route::post('projects/{project}/archive', [ProjectController::class, 'archive'])->name('projects.archive');
    Route::post('projects/{project}/restore', [ProjectController::class, 'restore'])->name('projects.restore');

    // 🚀 NEW: Project utility routes
    Route::prefix('projects/{project}')->group(function () {
        Route::get('api', [ProjectController::class, 'apiShow'])->name('projects.api');
        Route::get('statistics', [ProjectController::class, 'statistics'])->name('projects.statistics');
        Route::patch('status', [ProjectController::class, 'updateStatus'])->name('projects.status');

        // Inline edit routes
        Route::patch('update-basic-info', [ProjectController::class, 'updateBasicInfo'])->name('projects.update-basic-info');
        Route::patch('update-financial', [ProjectController::class, 'updateFinancial'])->name('projects.update-financial');

        // Team member management
        Route::get('team-data', [ProjectController::class, 'getTeamData'])->name('projects.team-data');
        Route::post('add-team-member', [ProjectController::class, 'addTeamMember'])->name('projects.add-team-member');
        Route::delete('remove-team-member', [ProjectController::class, 'removeTeamMember'])->name('projects.remove-team-member');
        Route::post('team/add', [ProjectController::class, 'addTeamMember'])->name('projects.team.add');
        Route::delete('team/remove', [ProjectController::class, 'removeTeamMember'])->name('projects.team.remove');
        Route::put('team/update', [ProjectController::class, 'updateTeamMember'])->name('projects.team.update');
        Route::put('team', [ProjectController::class, 'updateTeam'])->name('projects.updateTeam');

        // Service import
        Route::post('services/import', [ProjectController::class, 'importService'])->name('projects.services.import');

        // Monthly project structure for filtering
        Route::get('monthly-structure', [ProjectController::class, 'getMonthlyProjectStructure'])->name('projects.monthly-structure');

        // AJAX endpoint for month data
        Route::get('month-data', [ProjectController::class, 'getMonthData'])->name('projects.month-data');
    });

    // Detail modal endpoints
    Route::get('milestones/{milestone}/details', [ProjectController::class, 'getMilestoneDetails'])->name('milestones.details');
    Route::get('tasks/{task}/details', [ProjectController::class, 'getTaskDetails'])->name('tasks.details');
    Route::put('tasks/{task}/details', [ProjectController::class, 'updateTaskDetails'])->name('tasks.update-details');

    // =====================================
    // PROJECT MILESTONES 🚀 ENHANCED
    // =====================================
    Route::resource('projects.milestones', ProjectMilestoneController::class)->except(['index']);
    Route::get('projects/{project}/milestones', [ProjectMilestoneController::class, 'index'])->name('projects.milestones.index');
    Route::post('projects/{project}/milestones/reorder', [ProjectMilestoneController::class, 'reorder'])->name('projects.milestones.reorder');

    // 🚀 NEW: Milestone specific actions
    Route::prefix('projects/{project}/milestones/{milestone}')->group(function () {
        Route::patch('status', [ProjectMilestoneController::class, 'updateStatus'])->name('projects.milestones.status');
        Route::post('duplicate', [ProjectMilestoneController::class, 'duplicate'])->name('projects.milestones.duplicate');
        Route::get('api', [ProjectMilestoneController::class, 'apiShow'])->name('projects.milestones.api');
    });

    // =====================================
    // PROJECT TASKS 🚀 ENHANCED
    // =====================================
    Route::resource('project-milestones.tasks', ProjectTaskController::class)->except(['index']);
    Route::get('project-milestones/{projectMilestone}/tasks', [ProjectTaskController::class, 'index'])->name('project-milestones.tasks.index');
    Route::post('project-milestones/{projectMilestone}/tasks/reorder', [ProjectTaskController::class, 'reorder'])->name('project-milestones.tasks.reorder');

    // 🚀 NEW: Task specific actions (aangepast aan jouw route structuur)
    Route::prefix('project-milestones/{projectMilestone}/tasks/{task}')->group(function () {
        Route::patch('status', [ProjectTaskController::class, 'updateStatus'])->name('project-milestones.tasks.status');
        Route::post('duplicate', [ProjectTaskController::class, 'duplicate'])->name('project-milestones.tasks.duplicate');
        Route::patch('move', [ProjectTaskController::class, 'move'])->name('project-milestones.tasks.move');
        Route::get('api', [ProjectTaskController::class, 'apiShow'])->name('project-milestones.tasks.api');
    });

    // 🚀 NEW: Task bulk operations
    Route::patch('project-milestones/{projectMilestone}/tasks/bulk-status', [ProjectTaskController::class, 'bulkUpdateStatus'])->name('project-milestones.tasks.bulk-status');

    // =====================================
    // PROJECT SUBTASKS 🚀 ENHANCED
    // =====================================
    Route::resource('project-tasks.subtasks', ProjectSubtaskController::class)->except(['index']);
    Route::get('project-tasks/{projectTask}/subtasks', [ProjectSubtaskController::class, 'index'])->name('project-tasks.subtasks.index');
    Route::post('project-tasks/{projectTask}/subtasks/reorder', [ProjectSubtaskController::class, 'reorder'])->name('project-tasks.subtasks.reorder');

    // 🚀 NEW: Subtask specific actions (aangepast aan jouw route structuur)
    Route::prefix('project-tasks/{projectTask}/subtasks/{subtask}')->group(function () {
        Route::patch('status', [ProjectSubtaskController::class, 'updateStatus'])->name('project-tasks.subtasks.status');
        Route::post('duplicate', [ProjectSubtaskController::class, 'duplicate'])->name('project-tasks.subtasks.duplicate');
        Route::patch('move', [ProjectSubtaskController::class, 'move'])->name('project-tasks.subtasks.move');
        Route::get('api', [ProjectSubtaskController::class, 'apiShow'])->name('project-tasks.subtasks.api');
    });

    // 🚀 NEW: Subtask bulk operations
    Route::patch('project-tasks/{projectTask}/subtasks/bulk-status', [ProjectSubtaskController::class, 'bulkUpdateStatus'])->name('project-tasks.subtasks.bulk-status');

    // =====================================
    // PROJECT TEMPLATES
    // =====================================
    Route::resource('project-templates', ProjectTemplateController::class);
    Route::post('project-templates/{projectTemplate}/duplicate', [ProjectTemplateController::class, 'duplicate'])->name('project-templates.duplicate');
    Route::get('project-templates/{projectTemplate}/export', [ProjectTemplateController::class, 'export'])->name('project-templates.export');

    // =====================================
    // TIME TRACKING
    // =====================================
    Route::get('time-approvals', [TimeEntryController::class, 'approvals'])->name('time-entries.approvals');

    // TIME ENTRIES ROUTES
    Route::resource('time-entries', TimeEntryController::class);

    // AI TIME ENTRY ROUTES

    // AI Learning Review Routes
    Route::get('ai-learning', [AiLearningController::class, 'index'])->name('ai-learning.index');
    Route::post('ai-learning/feedback/{timeEntry}', [AiLearningController::class, 'updateFeedback'])->name('ai-learning.feedback');
    Route::post('ai-learning/bulk-review', [AiLearningController::class, 'bulkReview'])->name('ai-learning.bulk-review');
    Route::post('ai-learning/apply/{project}', [AiLearningController::class, 'applyLearning'])->name('ai-learning.apply');

    Route::resource('time-entries', TimeEntryController::class)->except(['index']);
    Route::post('time-entries/{timeEntry}/approve', [TimeEntryController::class, 'approve'])->name('time-entries.approve');
    Route::post('time-entries/{timeEntry}/reject', [TimeEntryController::class, 'reject'])->name('time-entries.reject');
    Route::post('time-entries/bulk-approve', [TimeEntryController::class, 'bulkApprove'])->name('time-entries.bulk-approve');
    Route::post('time-entries/bulk-reject', [TimeEntryController::class, 'bulkReject'])->name('time-entries.bulk-reject');
    Route::get('time-entries/export', [TimeEntryController::class, 'export'])->name('time-entries.export');

    // =====================================
    // PROJECT ADDITIONAL COSTS
    // =====================================
    Route::prefix('projects/{project}/additional-costs')->group(function () {
        Route::get('/', [ProjectAdditionalCostController::class, 'index'])->name('projects.additional-costs.index');
        Route::get('/create', [ProjectAdditionalCostController::class, 'create'])->name('projects.additional-costs.create');
        Route::post('/', [ProjectAdditionalCostController::class, 'store'])->name('projects.additional-costs.store');
        Route::get('/create-monthly', [ProjectAdditionalCostController::class, 'createMonthly'])->name('projects.additional-costs.create-monthly');
        Route::post('/monthly', [ProjectAdditionalCostController::class, 'storeMonthly'])->name('projects.additional-costs.store-monthly');
        Route::get('/{additionalCost}/edit', [ProjectAdditionalCostController::class, 'edit'])->name('projects.additional-costs.edit');
        Route::put('/{additionalCost}', [ProjectAdditionalCostController::class, 'update'])->name('projects.additional-costs.update');
        Route::delete('/{additionalCost}', [ProjectAdditionalCostController::class, 'destroy'])->name('projects.additional-costs.destroy');
        Route::post('/{additionalCost}/approve', [ProjectAdditionalCostController::class, 'approve'])->name('projects.additional-costs.approve');
        Route::patch('/monthly/{monthlyCost}/toggle', [ProjectAdditionalCostController::class, 'toggleMonthly'])->name('projects.additional-costs.toggle-monthly');
    });

    // =====================================
    // PROJECT MEDIA CAMPAIGNS
    // =====================================
    Route::prefix('projects/{project}/media-campaigns')->group(function () {
        Route::get('/', [ProjectMediaCampaignController::class, 'index'])->name('projects.media-campaigns.index');
        Route::get('/create', [ProjectMediaCampaignController::class, 'create'])->name('projects.media-campaigns.create');
        Route::post('/', [ProjectMediaCampaignController::class, 'store'])->name('projects.media-campaigns.store');
        Route::get('/{campaign}', [ProjectMediaCampaignController::class, 'show'])->name('projects.media-campaigns.show');
        Route::get('/{campaign}/edit', [ProjectMediaCampaignController::class, 'edit'])->name('projects.media-campaigns.edit');
        Route::put('/{campaign}', [ProjectMediaCampaignController::class, 'update'])->name('projects.media-campaigns.update');
        Route::delete('/{campaign}', [ProjectMediaCampaignController::class, 'destroy'])->name('projects.media-campaigns.destroy');
        Route::post('/{campaign}/link-mention', [ProjectMediaCampaignController::class, 'linkMention'])->name('projects.media-campaigns.link-mention');
        Route::delete('/{campaign}/unlink-mention/{mentionId}', [ProjectMediaCampaignController::class, 'unlinkMention'])->name('projects.media-campaigns.unlink-mention');
    });

    // =====================================
    // QUICK REPORTS
    // =====================================
    Route::prefix('reports')->group(function () {
        Route::get('/', [QuickReportsController::class, 'index'])->name('reports.quick-reports');
        Route::get('/weekly-timesheet', [QuickReportsController::class, 'weeklyTimesheet'])->name('reports.weekly-timesheet');
        Route::get('/weekly-timesheet/pdf', [QuickReportsController::class, 'weeklyTimesheetPdf'])->name('reports.weekly-timesheet-pdf');
        Route::get('/monthly-invoices', [QuickReportsController::class, 'monthlyInvoices'])->name('reports.monthly-invoices');
        Route::get('/project-profitability', [QuickReportsController::class, 'projectProfitability'])->name('reports.project-profitability');
        Route::get('/overdue-milestones', [QuickReportsController::class, 'overdueMilestones'])->name('reports.overdue-milestones');
    });

    // =====================================
    // INVOICE SYSTEM
    // =====================================
    Route::get('invoices/dashboard', [InvoiceDashboardController::class, 'index'])->name('invoices.dashboard');
    Route::post('invoices/generate', [InvoiceDashboardController::class, 'generateInvoices'])->name('invoices.generate');

    // AI INVOICE BUNDELING TEST 🤖 - MOET VOOR resource route!
    Route::get('invoices/ai-test', [InvoiceAITestController::class, 'index'])->name('invoices.ai-test');
    Route::post('invoices/ai-test/{project}/test', [InvoiceAITestController::class, 'testSummarization'])->name('invoices.ai-test.run');
    Route::post('invoices/ai-test/{project}/apply', [InvoiceAITestController::class, 'applyToInvoice'])->name('invoices.ai-test.apply');
    Route::get('invoices/ai-test/{project}/create', [InvoiceAITestController::class, 'createDirectInvoice'])->name('invoices.ai-test.create');
    Route::get('invoices/quick-create/{project}', [InvoiceAITestController::class, 'quickCreate'])->name('invoices.quick-create');

    // BELANGRIJK: Verplaats deze resource route naar beneden na alle custom routes
    // Route::resource('invoices', InvoiceController::class);

    // =====================================
    // INVOICE TEMPLATES
    // =====================================
    Route::resource('invoice-templates', InvoiceTemplateController::class);
    Route::post('invoice-templates/{invoiceTemplate}/duplicate', [InvoiceTemplateController::class, 'duplicate'])->name('invoice-templates.duplicate');
    Route::match(['get', 'post'], 'invoice-templates/{invoiceTemplate}/preview', [InvoiceTemplateController::class, 'preview'])->name('invoice-templates.preview');
    Route::get('invoice-templates/{invoiceTemplate}/preview-ajax', [InvoiceTemplateController::class, 'previewAjax'])->name('invoice-templates.preview-ajax');
    Route::post('invoice-templates/preview-new', [InvoiceTemplateController::class, 'previewNew'])->name('invoice-templates.preview-new');
    Route::get('invoice-templates-help', [InvoiceTemplateController::class, 'help'])->name('invoice-templates.help');

    // =====================================
    // MICROSOFT GRAPH OAUTH
    // =====================================
    Route::get('msgraph/connect', [MsGraphAuthController::class, 'connect'])->name('msgraph.connect');
    Route::get('msgraph/oauth', [MsGraphAuthController::class, 'connect'])->name('msgraph.oauth');
    Route::post('msgraph/disconnect', [MsGraphAuthController::class, 'disconnect'])->name('msgraph.disconnect');

    // =====================================
    // CALENDAR & MICROSOFT 365 INTEGRATION
    // =====================================
    Route::get('calendar', [\App\Http\Controllers\MultiCalendarController::class, 'calendarIndex'])->name('calendar.index');
    Route::get('calendar/manual', function() {
        $events = \App\Models\CalendarEvent::where('user_id', Auth::id())
            ->orderBy('start_datetime', 'desc')
            ->get();
        return view('calendar.manual', compact('events'));
    })->name('calendar.manual');
    Route::post('calendar/manual/store', [CalendarController::class, 'manualStore'])->name('calendar.manual.store');
    Route::post('calendar/import', [CalendarController::class, 'import'])->name('calendar.import');
    Route::get('calendar/setup', function() {
        return view('calendar.setup');
    })->name('calendar.setup');
    Route::get('calendar/connect', [CalendarController::class, 'connect'])->name('calendar.connect');
    Route::get('calendar/admin-consent', function() {
        return view('calendar.admin-consent');
    })->name('calendar.admin-consent');
    Route::get('calendar/switch-account', function() {
        return view('calendar.switch-account');
    })->name('calendar.switch-account');
    Route::get('calendar/force-logout', function() {
        return view('calendar.force-logout');
    })->name('calendar.force-logout');
    Route::get('calendar/select-account', function() {
        return view('calendar.account-selector');
    })->name('calendar.select-account');
    Route::get('calendar/disconnect', function() {
        return view('calendar.disconnect');
    })->name('calendar.disconnect.page');
    Route::post('calendar/sync', [\App\Http\Controllers\MultiCalendarController::class, 'syncAll'])->name('calendar.sync');
    Route::post('calendar/ajax-sync', [CalendarController::class, 'ajaxSync'])->name('calendar.ajax-sync');
    Route::post('calendar/store', [CalendarController::class, 'store'])->name('calendar.store');
    Route::post('calendar/disconnect', [CalendarController::class, 'disconnect'])->name('calendar.disconnect');
    Route::get('calendar/events', [CalendarController::class, 'getEvents'])->name('calendar.events');

    // New event creation and editing routes
    Route::post('calendar/events/create', [\App\Http\Controllers\MultiCalendarController::class, 'createEvent'])->name('calendar.events.store');
    Route::put('calendar/events/{event}', [\App\Http\Controllers\MultiCalendarController::class, 'updateEvent'])->name('calendar.events.update');
    Route::delete('calendar/events/{event}/delete', [\App\Http\Controllers\MultiCalendarController::class, 'deleteEvent'])->name('calendar.events.delete');
    Route::get('calendar/event-details/{event}', [\App\Http\Controllers\MultiCalendarController::class, 'getEventDetails'])->name('calendar.events.details');
    Route::post('calendar/events/{calendarEvent}/convert', [CalendarController::class, 'convertToTimeEntry'])->name('calendar.convert');
    Route::get('calendar/events/{calendarEvent}/ai-predictions', [CalendarController::class, 'getAIPredictions'])->name('calendar.ai-predictions');
    Route::post('calendar/bulk-convert', [CalendarController::class, 'bulkConvert'])->name('calendar.bulk-convert');
    Route::get('calendar/events/{event}/respond', [CalendarController::class, 'respondToInvitation'])->name('calendar.respond');
    Route::delete('calendar/events/{event}/cancel', [CalendarController::class, 'cancelEvent'])->name('calendar.cancel');
    Route::delete('calendar/events/{event}', [CalendarController::class, 'destroy'])->name('calendar.destroy');

    // Calendar API endpoints for dynamic dropdowns
    Route::get('api/calendar/projects/{project}/milestones', [CalendarController::class, 'getProjectMilestones'])->name('api.calendar.milestones');
    Route::get('api/calendar/milestones/{milestone}/tasks', [CalendarController::class, 'getMilestoneTasks'])->name('api.calendar.tasks');
    Route::get('api/calendar/tasks/{task}/subtasks', [CalendarController::class, 'getTaskSubtasks'])->name('api.calendar.subtasks');

    // Multi-Provider Calendar Routes
    Route::prefix('calendar/providers')->group(function () {
        // Provider settings and management
        Route::get('/', [\App\Http\Controllers\MultiCalendarController::class, 'index'])->name('calendar.providers.index');

        // Microsoft provider routes
        Route::get('microsoft/connect', [\App\Http\Controllers\MultiCalendarController::class, 'connectMicrosoft'])->name('calendar.providers.microsoft.connect');
        Route::get('microsoft/callback', [\App\Http\Controllers\MultiCalendarController::class, 'callbackMicrosoft'])->name('calendar.providers.microsoft.callback');
        Route::post('microsoft/disconnect', [\App\Http\Controllers\MultiCalendarController::class, 'disconnectMicrosoft'])->name('calendar.providers.microsoft.disconnect');

        // Google provider routes
        Route::get('google/setup', [\App\Http\Controllers\MultiCalendarController::class, 'setupGoogle'])->name('calendar.providers.google.setup');
        Route::post('google/store', [\App\Http\Controllers\MultiCalendarController::class, 'storeGoogle'])->name('calendar.providers.google.store');
        Route::get('google/connect', [\App\Http\Controllers\MultiCalendarController::class, 'connectGoogle'])->name('calendar.providers.google.connect');
        Route::get('google/callback', [\App\Http\Controllers\MultiCalendarController::class, 'callbackGoogle'])->name('calendar.providers.google.callback');
        Route::post('google/disconnect', [\App\Http\Controllers\MultiCalendarController::class, 'disconnectGoogle'])->name('calendar.providers.google.disconnect');

        // Apple provider routes
        Route::get('apple/setup', [\App\Http\Controllers\MultiCalendarController::class, 'setupApple'])->name('calendar.providers.apple.setup');
        Route::post('apple/store', [\App\Http\Controllers\MultiCalendarController::class, 'storeApple'])->name('calendar.providers.apple.store');
        Route::post('apple/disconnect', [\App\Http\Controllers\MultiCalendarController::class, 'disconnectApple'])->name('calendar.providers.apple.disconnect');

        // Sync routes
        Route::post('sync/{provider}', [\App\Http\Controllers\MultiCalendarController::class, 'syncProvider'])->name('calendar.providers.sync');
        Route::post('sync-all', [\App\Http\Controllers\MultiCalendarController::class, 'syncAll'])->name('calendar.providers.sync-all');
    });

    // Generic API routes for project hierarchy (used by calendar create modal)
    Route::get('api/projects/{project}/milestones', [CalendarController::class, 'getProjectMilestones']);
    Route::get('api/milestones/{milestone}/tasks', [CalendarController::class, 'getMilestoneTasks']);
    Route::get('api/tasks/{task}/subtasks', [CalendarController::class, 'getTaskSubtasks']);

    // =====================================
    // AI PROJECT INTELLIGENCE
    // =====================================
    Route::prefix('project-intelligence')->group(function () {
        Route::get('/', [ProjectIntelligenceController::class, 'index'])->name('project-intelligence.index');
        Route::get('/{project}', [ProjectIntelligenceController::class, 'show'])->name('project-intelligence.show');
        Route::post('/{project}/refresh', [ProjectIntelligenceController::class, 'refresh'])->name('project-intelligence.refresh');
        Route::get('/{project}/recommendations', [ProjectIntelligenceController::class, 'getRecommendations'])->name('project-intelligence.recommendations');
    });

    // =====================================
    // AI CHAT ASSISTANT
    // =====================================
    Route::prefix('ai-chat')->group(function () {
        Route::post('/chat', [AIChatController::class, 'chat'])->name('ai-chat.chat');
        Route::get('/history', [AIChatController::class, 'history'])->name('ai-chat.history');
        Route::post('/clear-history', [AIChatController::class, 'clearHistory'])->name('ai-chat.clear-history');
        Route::get('/suggestions', [AIChatController::class, 'suggestions'])->name('ai-chat.suggestions');
    });

    // =====================================
    // AI WEEKLY DIGEST
    // =====================================
    Route::prefix('ai-digest')->group(function () {
        Route::get('/', [AIDigestController::class, 'index'])->name('ai-digest.index');
        Route::put('/settings', [AIDigestController::class, 'updateSettings'])->name('ai-digest.settings');
        Route::post('/generate', [AIDigestController::class, 'generate'])->name('ai-digest.generate');
        Route::get('/preview', [AIDigestController::class, 'preview'])->name('ai-digest.preview');
        Route::get('/download', [AIDigestController::class, 'download'])->name('ai-digest.download');
    });

    // =====================================
    // SETTINGS
    // =====================================
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('settings/ai-usage', [SettingsController::class, 'aiUsage'])->name('settings.ai-usage');
    Route::get('settings/ai-invoice-prompts', [SettingsController::class, 'aiInvoicePrompts'])->name('settings.ai-invoice-prompts');
    Route::put('settings/ai-invoice-prompts', [SettingsController::class, 'updateAiInvoicePrompts'])->name('settings.ai-invoice-prompts.update');

    // System Status Dashboard
    Route::get('system/status', [\App\Http\Controllers\SystemStatusController::class, 'index'])->name('system.status');
    Route::post('system/status/refresh', [\App\Http\Controllers\SystemStatusController::class, 'refresh'])->name('system.status.refresh');

    // AI Settings - Centralized AI configuration
    Route::get('ai-settings', [\App\Http\Controllers\AiSettingsController::class, 'index'])->name('ai-settings.index');
    Route::put('ai-settings', [\App\Http\Controllers\AiSettingsController::class, 'update'])->name('ai-settings.update');
    Route::post('ai-settings/test-connection', [\App\Http\Controllers\AiSettingsController::class, 'testConnection'])->name('ai-settings.test-connection');
    Route::post('ai-settings/reset-usage', [\App\Http\Controllers\AiSettingsController::class, 'resetUsage'])->name('ai-settings.reset-usage');
    Route::get('ai-settings/export-usage', [\App\Http\Controllers\AiSettingsController::class, 'exportUsage'])->name('ai-settings.export-usage');

    // Theme Settings - Plugin-based customization per installation
    // Simplified Theme Settings
    Route::get('settings/theme', [\App\Http\Controllers\SimplifiedThemeSettingsController::class, 'index'])->name('settings.theme');
    Route::post('settings/theme/update', [\App\Http\Controllers\SimplifiedThemeSettingsController::class, 'update'])->name('settings.theme.update');
    Route::post('settings/theme/preset/{preset}', [\App\Http\Controllers\SimplifiedThemeSettingsController::class, 'applyPreset'])->name('settings.theme.preset');
    Route::post('settings/theme/reset', [\App\Http\Controllers\SimplifiedThemeSettingsController::class, 'reset'])->name('settings.theme.reset');
    Route::post('settings/theme/preview', [\App\Http\Controllers\SimplifiedThemeSettingsController::class, 'preview'])->name('settings.theme.preview');

    // =====================================
    // PLUGIN MANAGEMENT - REMOVED
    // =====================================
    // Plugin system has been removed

    // Invoice routes
    Route::post('invoices/generate', [InvoiceController::class, 'generate'])->name('invoices.generate');
    Route::put('invoices/{invoice}/status', [InvoiceController::class, 'updateStatus'])->name('invoices.status');
    Route::post('invoices/{invoice}/line', [InvoiceController::class, 'addLine'])->name('invoices.line.add');
    Route::delete('invoices/{invoice}/line/{line}', [InvoiceController::class, 'deleteLine'])->name('invoices.line.delete');
    Route::put('invoices/{invoice}/line/{line}', [InvoiceController::class, 'updateLine'])->name('invoices.line.update');
    Route::post('invoices/{invoice}/finalize', [InvoiceController::class, 'finalize'])->name('invoices.finalize');
    Route::post('invoices/{invoice}/execute-defers', [InvoiceController::class, 'executeDefers'])->name('invoices.execute-defers');
    Route::post('invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('invoices.send');
    Route::post('invoices/{invoice}/mark-paid', [InvoiceController::class, 'markPaid'])->name('invoices.mark-paid');
    Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');
    Route::get('invoices/export', [InvoiceController::class, 'export'])->name('invoices.export');

    // AI Invoice Generation Routes
    Route::get('invoices/create-ai', [InvoiceController::class, 'createWithAI'])->name('invoices.create-ai');
    Route::post('invoices/generate-ai', [InvoiceController::class, 'generateWithAI'])->name('invoices.generate-ai');
    Route::post('invoices/{invoice}/regenerate-ai', [InvoiceController::class, 'regenerateAI'])->name('invoices.regenerate-ai');
    Route::get('invoices/{invoice}/activity-report', [InvoiceController::class, 'previewActivityReport'])->name('invoices.activity-report');
    Route::get('invoices/{invoice}/export-activity-report', [InvoiceController::class, 'exportActivityReport'])->name('invoices.export-activity-report');
    Route::put('invoices/{invoice}/update-descriptions', [InvoiceController::class, 'updateActivityDescriptions'])->name('invoices.update-descriptions');
    Route::get('invoices-help', [InvoiceController::class, 'help'])->name('invoices.help');

    // RESOURCE ROUTE - MOET ALS LAATSTE NA ALLE CUSTOM ROUTES!
    Route::resource('invoices', InvoiceController::class);

    // =====================================
    // SERVICE CATALOG 🚀 MET STRUCTURE ROUTES
    // =====================================

    // Service Categories
    Route::resource('service-categories', ServiceCategoryController::class);

    // Services
    Route::resource('services', ServiceController::class);
    Route::post('services/{service}/duplicate', [ServiceController::class, 'duplicate'])->name('services.duplicate');
    Route::get('services/{service}/export', [ServiceController::class, 'export'])->name('services.export');

    // 🚀 SERVICE STRUCTURE ROUTE (NIEUW!)
    Route::get('services/{service}/structure', [ServiceController::class, 'structure'])->name('services.structure');

    // 🚀 SERVICE MILESTONES (NIEUW!)
    Route::post('services/{service}/milestones', [ServiceMilestoneController::class, 'store'])->name('services.milestones.store');
    Route::resource('services.milestones', ServiceMilestoneController::class)->except(['index', 'store']);
    Route::get('services/{service}/milestones', [ServiceMilestoneController::class, 'index'])->name('services.milestones.index');
    Route::post('services/{service}/milestones/reorder', [ServiceMilestoneController::class, 'reorder'])->name('services.milestones.reorder');

    // Direct milestone routes for AJAX
    Route::get('service-milestones/{milestone}/edit', [ServiceMilestoneController::class, 'ajaxEdit'])->name('service-milestones.ajax-edit');
    Route::put('service-milestones/{milestone}', [ServiceMilestoneController::class, 'ajaxUpdate'])->name('service-milestones.ajax-update');
    Route::delete('service-milestones/{milestone}', [ServiceMilestoneController::class, 'ajaxDestroy'])->name('service-milestones.ajax-destroy');

    // 🚀 SERVICE TASKS (NIEUW!)
    Route::post('services/{service}/milestones/{milestone}/tasks', [ServiceTaskController::class, 'store'])->name('services.milestones.tasks.store');
    Route::resource('service-milestones.tasks', ServiceTaskController::class)->except(['index', 'store']);
    Route::get('service-milestones/{serviceMilestone}/tasks', [ServiceTaskController::class, 'index'])->name('service-milestones.tasks.index');
    Route::post('service-milestones/{serviceMilestone}/tasks/reorder', [ServiceTaskController::class, 'reorder'])->name('service-milestones.tasks.reorder');

    // Direct task routes for AJAX
    Route::get('service-tasks/{task}/edit', [ServiceTaskController::class, 'ajaxEdit'])->name('service-tasks.ajax-edit');
    Route::put('service-tasks/{task}', [ServiceTaskController::class, 'ajaxUpdate'])->name('service-tasks.ajax-update');
    Route::delete('service-tasks/{task}', [ServiceTaskController::class, 'ajaxDestroy'])->name('service-tasks.ajax-destroy');

    // 🚀 SERVICE SUBTASKS (NIEUW!)
    Route::post('services/tasks/{task}/subtasks', [ServiceSubtaskController::class, 'store'])->name('services.tasks.subtasks.store');
    Route::resource('service-tasks.subtasks', ServiceSubtaskController::class)->except(['index', 'store']);
    Route::get('service-tasks/{serviceTask}/subtasks', [ServiceSubtaskController::class, 'index'])->name('service-tasks.subtasks.index');
    Route::post('service-tasks/{serviceTask}/subtasks/reorder', [ServiceSubtaskController::class, 'reorder'])->name('service-tasks.subtasks.reorder');

    // Direct subtask routes for AJAX
    Route::get('service-subtasks/{subtask}/edit', [ServiceSubtaskController::class, 'ajaxEdit'])->name('service-subtasks.ajax-edit');
    Route::put('service-subtasks/{subtask}', [ServiceSubtaskController::class, 'ajaxUpdate'])->name('service-subtasks.ajax-update');
    Route::delete('service-subtasks/{subtask}', [ServiceSubtaskController::class, 'ajaxDestroy'])->name('service-subtasks.ajax-destroy');
});

// =====================================
// API ROUTES 🚀 NEW (Optional - for AJAX calls)
// =====================================

Route::prefix('api')->middleware(['auth'])->group(function () {
    // Company users endpoint
    Route::get('companies/{company}/users', [CompanyController::class, 'getUsers'])->name('api.companies.users');

    // Project API endpoints - Commented out as these methods don't exist
    // Route::get('projects/{project}/milestones', [ProjectMilestoneController::class, 'apiIndex'])->name('api.projects.milestones');
    // Route::get('project-milestones/{projectMilestone}/tasks', [ProjectTaskController::class, 'apiIndex'])->name('api.project-milestones.tasks');
    // Route::get('project-tasks/{projectTask}/subtasks', [ProjectSubtaskController::class, 'apiIndex'])->name('api.project-tasks.subtasks');

    // Quick status updates via API
    Route::patch('projects/{project}/status', [ProjectController::class, 'updateStatus'])->name('api.projects.update-status');
    Route::patch('project-milestones/{milestone}/status', [ProjectMilestoneController::class, 'updateStatus'])->name('api.milestones.update-status');
    Route::patch('project-tasks/{task}/status', [ProjectTaskController::class, 'updateStatus'])->name('api.tasks.update-status');
    Route::patch('project-subtasks/{subtask}/status', [ProjectSubtaskController::class, 'updateStatus'])->name('api.subtasks.update-status');
});

// Protected routes that need authentication
Route::middleware(['auth'])->group(function () {
    // =====================================
    // TIME ENTRIES 🕒 NEW! (21-08-2025)
    // =====================================
    Route::get('time-entries/{timeEntry}/edit-modal', [TimeEntryController::class, 'editModal'])
        ->name('time-entries.edit-modal');
    Route::get('time-entries/{timeEntry}/show-modal', [TimeEntryController::class, 'showModal'])
        ->name('time-entries.show-modal');
    Route::get('time-entries/project/{project}/work-items', [TimeEntryController::class, 'getWorkItems'])
        ->name('time-entries.work-items');
    Route::resource('time-entries', TimeEntryController::class);
});
