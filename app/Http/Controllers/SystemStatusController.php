<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use App\Models\Company;
use App\Models\Project;
use App\Models\TimeEntry;
use App\Models\Invoice;
use App\Models\AIUsageLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class SystemStatusController extends Controller
{
    /**
     * Display system status dashboard
     */
    public function index()
    {
        // Only super_admin can access system status
        if (Auth::user()->role !== 'super_admin') {
            abort(403, 'Access denied. Only super administrators can view system status.');
        }

        $systemStatus = [
            'overall' => 'healthy', // healthy, warning, critical
            'last_check' => now(),
            'sections' => [
                'database' => $this->checkDatabase(),
                'storage' => $this->checkStorage(),
                'email' => $this->checkEmail(),
                'cache' => $this->checkCache(),
                'ai_services' => $this->checkAIServices(),
                'external_apis' => $this->checkExternalAPIs(),
                'background_jobs' => $this->checkBackgroundJobs(),
                'security' => $this->checkSecurity(),
            ]
        ];

        // Determine overall system health
        $criticalIssues = 0;
        $warnings = 0;

        foreach ($systemStatus['sections'] as $section) {
            if ($section['status'] === 'critical') $criticalIssues++;
            if ($section['status'] === 'warning') $warnings++;
        }

        if ($criticalIssues > 0) {
            $systemStatus['overall'] = 'critical';
        } elseif ($warnings > 0) {
            $systemStatus['overall'] = 'warning';
        }

        return view('system.status', compact('systemStatus'));
    }

    /**
     * Check database health
     */
    private function checkDatabase()
    {
        $status = 'healthy';
        $checks = [];
        $metrics = [];

        try {
            // Test database connection
            $start = microtime(true);
            DB::connection()->getPdo();
            $connectionTime = round((microtime(true) - $start) * 1000, 2);

            $checks[] = [
                'name' => 'Database Connection',
                'status' => 'healthy',
                'message' => "Connected in {$connectionTime}ms"
            ];

            // Get database size and table counts
            $tableCount = DB::select("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = DATABASE()")[0]->count;

            $metrics[] = ['label' => 'Tables', 'value' => $tableCount];
            $metrics[] = ['label' => 'Companies', 'value' => Company::count()];
            $metrics[] = ['label' => 'Users', 'value' => User::count()];
            $metrics[] = ['label' => 'Projects', 'value' => Project::count()];
            $metrics[] = ['label' => 'Time Entries', 'value' => TimeEntry::count()];

            // Check for recent data activity
            $recentTimeEntries = TimeEntry::where('created_at', '>=', Carbon::now()->subDays(7))->count();
            $recentInvoices = Invoice::where('created_at', '>=', Carbon::now()->subDays(30))->count();

            $metrics[] = ['label' => 'Time Entries (7d)', 'value' => $recentTimeEntries];
            $metrics[] = ['label' => 'Invoices (30d)', 'value' => $recentInvoices];

        } catch (\Exception $e) {
            $status = 'critical';
            $checks[] = [
                'name' => 'Database Connection',
                'status' => 'critical',
                'message' => 'Connection failed: ' . $e->getMessage()
            ];
        }

        return [
            'status' => $status,
            'title' => 'Database',
            'icon' => 'fas fa-database',
            'checks' => $checks,
            'metrics' => $metrics
        ];
    }

    /**
     * Check storage and file system
     */
    private function checkStorage()
    {
        $status = 'healthy';
        $checks = [];
        $metrics = [];

        try {
            // Check disk space
            $totalSpace = disk_total_space('/');
            $freeSpace = disk_free_space('/');
            $usedSpace = $totalSpace - $freeSpace;
            $usagePercentage = round(($usedSpace / $totalSpace) * 100, 1);

            if ($usagePercentage > 90) {
                $status = 'critical';
                $checks[] = [
                    'name' => 'Disk Space',
                    'status' => 'critical',
                    'message' => "Disk usage critical: {$usagePercentage}%"
                ];
            } elseif ($usagePercentage > 80) {
                $status = 'warning';
                $checks[] = [
                    'name' => 'Disk Space',
                    'status' => 'warning',
                    'message' => "Disk usage high: {$usagePercentage}%"
                ];
            } else {
                $checks[] = [
                    'name' => 'Disk Space',
                    'status' => 'healthy',
                    'message' => "Disk usage: {$usagePercentage}%"
                ];
            }

            $metrics[] = ['label' => 'Disk Usage', 'value' => $usagePercentage . '%'];
            $metrics[] = ['label' => 'Free Space', 'value' => $this->formatBytes($freeSpace)];

            // Check storage directories
            $storageWritable = is_writable(storage_path());
            $checks[] = [
                'name' => 'Storage Writable',
                'status' => $storageWritable ? 'healthy' : 'critical',
                'message' => $storageWritable ? 'Storage directory is writable' : 'Storage directory is not writable'
            ];

            // Check log file sizes
            $logPath = storage_path('logs/laravel.log');
            if (file_exists($logPath)) {
                $logSize = filesize($logPath);
                $logSizeMB = round($logSize / 1024 / 1024, 2);

                if ($logSizeMB > 100) {
                    $status = ($status === 'critical') ? 'critical' : 'warning';
                }

                $metrics[] = ['label' => 'Log File Size', 'value' => $logSizeMB . ' MB'];
            }

        } catch (\Exception $e) {
            $status = 'critical';
            $checks[] = [
                'name' => 'Storage Check',
                'status' => 'critical',
                'message' => 'Storage check failed: ' . $e->getMessage()
            ];
        }

        return [
            'status' => $status,
            'title' => 'Storage & Files',
            'icon' => 'fas fa-hdd',
            'checks' => $checks,
            'metrics' => $metrics
        ];
    }

    /**
     * Check email system
     */
    private function checkEmail()
    {
        $status = 'healthy';
        $checks = [];
        $metrics = [];

        try {
            // Check if sendmail is configured
            $mailDriver = config('mail.default');
            $checks[] = [
                'name' => 'Mail Driver',
                'status' => 'healthy',
                'message' => "Using driver: {$mailDriver}"
            ];

            // Check sendmail path if using sendmail
            if ($mailDriver === 'sendmail') {
                $sendmailPath = config('mail.mailers.sendmail.path');
                $sendmailExists = file_exists(explode(' ', $sendmailPath)[0]);

                $checks[] = [
                    'name' => 'Sendmail Binary',
                    'status' => $sendmailExists ? 'healthy' : 'warning',
                    'message' => $sendmailExists ? "Sendmail found at {$sendmailPath}" : "Sendmail binary not found"
                ];

                if (!$sendmailExists && $status === 'healthy') {
                    $status = 'warning';
                }
            }

            $metrics[] = ['label' => 'Mail Driver', 'value' => ucfirst($mailDriver)];
            $metrics[] = ['label' => 'From Address', 'value' => config('mail.from.address')];

        } catch (\Exception $e) {
            $status = 'critical';
            $checks[] = [
                'name' => 'Email Configuration',
                'status' => 'critical',
                'message' => 'Email check failed: ' . $e->getMessage()
            ];
        }

        return [
            'status' => $status,
            'title' => 'Email System',
            'icon' => 'fas fa-envelope',
            'checks' => $checks,
            'metrics' => $metrics
        ];
    }

    /**
     * Check cache system
     */
    private function checkCache()
    {
        $status = 'healthy';
        $checks = [];
        $metrics = [];

        try {
            // Test cache read/write
            $testKey = 'system_status_test_' . time();
            $testValue = 'test_value_' . rand(1000, 9999);

            Cache::put($testKey, $testValue, 60);
            $retrievedValue = Cache::get($testKey);
            Cache::forget($testKey);

            if ($retrievedValue === $testValue) {
                $checks[] = [
                    'name' => 'Cache Read/Write',
                    'status' => 'healthy',
                    'message' => 'Cache is working correctly'
                ];
            } else {
                $status = 'warning';
                $checks[] = [
                    'name' => 'Cache Read/Write',
                    'status' => 'warning',
                    'message' => 'Cache test failed'
                ];
            }

            $cacheDriver = config('cache.default');
            $metrics[] = ['label' => 'Cache Driver', 'value' => ucfirst($cacheDriver)];

        } catch (\Exception $e) {
            $status = 'critical';
            $checks[] = [
                'name' => 'Cache System',
                'status' => 'critical',
                'message' => 'Cache check failed: ' . $e->getMessage()
            ];
        }

        return [
            'status' => $status,
            'title' => 'Cache System',
            'icon' => 'fas fa-memory',
            'checks' => $checks,
            'metrics' => $metrics
        ];
    }

    /**
     * Check AI services
     */
    private function checkAIServices()
    {
        $status = 'healthy';
        $checks = [];
        $metrics = [];

        try {
            // Check OpenAI configuration
            $openaiKey = config('openai.api_key');
            if (empty($openaiKey)) {
                $status = 'warning';
                $checks[] = [
                    'name' => 'OpenAI API Key',
                    'status' => 'warning',
                    'message' => 'OpenAI API key not configured'
                ];
            } else {
                $checks[] = [
                    'name' => 'OpenAI API Key',
                    'status' => 'healthy',
                    'message' => 'API key configured'
                ];
            }

            // Get AI usage statistics
            $todayUsage = AIUsageLog::whereDate('created_at', today())->count();
            $monthUsage = AIUsageLog::whereMonth('created_at', now()->month)->count();
            $totalCost = AIUsageLog::sum('estimated_cost') * 0.92; // Convert to EUR

            $metrics[] = ['label' => 'Requests Today', 'value' => $todayUsage];
            $metrics[] = ['label' => 'Requests This Month', 'value' => $monthUsage];
            $metrics[] = ['label' => 'Total Cost (EUR)', 'value' => '€' . number_format($totalCost, 2)];

            // Check for recent failures
            $recentFailures = AIUsageLog::where('created_at', '>=', Carbon::now()->subHours(24))
                ->where('response', 'like', '%error%')
                ->count();

            if ($recentFailures > 10) {
                $status = ($status === 'warning') ? 'warning' : 'warning';
                $checks[] = [
                    'name' => 'Recent AI Failures',
                    'status' => 'warning',
                    'message' => "{$recentFailures} failures in last 24 hours"
                ];
            }

        } catch (\Exception $e) {
            $status = 'critical';
            $checks[] = [
                'name' => 'AI Services',
                'status' => 'critical',
                'message' => 'AI services check failed: ' . $e->getMessage()
            ];
        }

        return [
            'status' => $status,
            'title' => 'AI Services',
            'icon' => 'fas fa-robot',
            'checks' => $checks,
            'metrics' => $metrics
        ];
    }

    /**
     * Check external APIs
     */
    private function checkExternalAPIs()
    {
        $status = 'healthy';
        $checks = [];
        $metrics = [];

        // Check Microsoft Graph API
        $graphClientId = Setting::get('msgraph_client_id', '');
        if (!empty($graphClientId)) {
            $checks[] = [
                'name' => 'Microsoft Graph',
                'status' => 'healthy',
                'message' => 'Configuration present'
            ];
            $metrics[] = ['label' => 'Graph Client ID', 'value' => 'Configured'];
        } else {
            $status = 'warning';
            $checks[] = [
                'name' => 'Microsoft Graph',
                'status' => 'warning',
                'message' => 'Not configured'
            ];
        }

        // Check internet connectivity
        try {
            $response = Http::timeout(5)->get('https://httpbin.org/status/200');
            if ($response->successful()) {
                $checks[] = [
                    'name' => 'Internet Connectivity',
                    'status' => 'healthy',
                    'message' => 'Connection successful'
                ];
            } else {
                $status = 'warning';
                $checks[] = [
                    'name' => 'Internet Connectivity',
                    'status' => 'warning',
                    'message' => 'Connection issues detected'
                ];
            }
        } catch (\Exception $e) {
            $status = 'warning';
            $checks[] = [
                'name' => 'Internet Connectivity',
                'status' => 'warning',
                'message' => 'Connection test failed'
            ];
        }

        return [
            'status' => $status,
            'title' => 'External APIs',
            'icon' => 'fas fa-globe',
            'checks' => $checks,
            'metrics' => $metrics
        ];
    }

    /**
     * Check background jobs and cron tasks
     */
    private function checkBackgroundJobs()
    {
        $status = 'healthy';
        $checks = [];
        $metrics = [];

        // Check calendar sync settings
        $calendarAutoSync = Setting::get('calendar_auto_sync', 'false') === 'true';
        $syncInterval = Setting::get('calendar_auto_sync_interval', '60');

        $checks[] = [
            'name' => 'Calendar Auto Sync',
            'status' => $calendarAutoSync ? 'healthy' : 'warning',
            'message' => $calendarAutoSync ? "Enabled (every {$syncInterval} minutes)" : 'Disabled'
        ];

        // Check invoice auto generation
        $invoiceAutoGen = Setting::get('invoice_auto_generate', 'false') === 'true';
        $checks[] = [
            'name' => 'Invoice Auto Generation',
            'status' => $invoiceAutoGen ? 'healthy' : 'warning',
            'message' => $invoiceAutoGen ? 'Enabled' : 'Disabled'
        ];

        $metrics[] = ['label' => 'Calendar Sync', 'value' => $calendarAutoSync ? 'Enabled' : 'Disabled'];
        $metrics[] = ['label' => 'Auto Invoicing', 'value' => $invoiceAutoGen ? 'Enabled' : 'Disabled'];

        return [
            'status' => $status,
            'title' => 'Background Jobs',
            'icon' => 'fas fa-cogs',
            'checks' => $checks,
            'metrics' => $metrics
        ];
    }

    /**
     * Check security status
     */
    private function checkSecurity()
    {
        $status = 'healthy';
        $checks = [];
        $metrics = [];

        // Check active user sessions
        $activeSessions = User::where('is_active', true)->count();
        $totalUsers = User::count();

        $metrics[] = ['label' => 'Active Users', 'value' => $activeSessions];
        $metrics[] = ['label' => 'Total Users', 'value' => $totalUsers];

        // Check for admin users
        $adminUsers = User::whereIn('role', ['super_admin', 'admin'])->count();
        $metrics[] = ['label' => 'Admin Users', 'value' => $adminUsers];

        // Check app environment
        $environment = app()->environment();
        if ($environment === 'production') {
            $checks[] = [
                'name' => 'Environment',
                'status' => 'healthy',
                'message' => 'Running in production mode'
            ];
        } else {
            $status = 'warning';
            $checks[] = [
                'name' => 'Environment',
                'status' => 'warning',
                'message' => "Running in {$environment} mode"
            ];
        }

        // Check debug mode
        $debugMode = config('app.debug');
        if ($debugMode && $environment === 'production') {
            $status = 'critical';
            $checks[] = [
                'name' => 'Debug Mode',
                'status' => 'critical',
                'message' => 'Debug mode enabled in production!'
            ];
        } else {
            $checks[] = [
                'name' => 'Debug Mode',
                'status' => 'healthy',
                'message' => $debugMode ? 'Enabled (development)' : 'Disabled'
            ];
        }

        return [
            'status' => $status,
            'title' => 'Security',
            'icon' => 'fas fa-shield-alt',
            'checks' => $checks,
            'metrics' => $metrics
        ];
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Refresh system status (AJAX endpoint)
     */
    public function refresh()
    {
        if (Auth::user()->role !== 'super_admin') {
            abort(403);
        }

        // Clear any cached data that might affect status checks
        Cache::flush();

        return $this->index();
    }
}