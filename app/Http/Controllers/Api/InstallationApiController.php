<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InstallationApiController extends Controller
{
    /**
     * Get installation informatie
     */
    public function info(): JsonResponse
    {
        try {
            // Alleen super_admin en admin hebben toegang
            if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Only administrators can access installation information.'
                ], 403);
            }

            // Basis installatie informatie
            $installationInfo = [
                'installation_name' => config('app.name', 'AdCompro Progress'),
                'installation_url' => config('app.url'),
                'environment' => config('app.env'),
                'version' => '1.0.0', // Je kunt dit uit een version file halen
                'laravel_version' => app()->version(),
                'timezone' => config('app.timezone'),
                'last_updated' => now()->toISOString(),
            ];

            // Database statistieken
            $statistics = [
                'companies' => Company::count(),
                'users' => User::count(),
                'projects' => Project::count(),
                'active_projects' => Project::where('status', 'active')->count(),
                'plugins_total' => 0,
                'plugins_active' => 0,
                'plugins_core' => 0,
            ];

            // Plugin informatie - plugins are no longer used
            $plugins = collect([]);

            // Systeemstatus
            $systemStatus = $this->getSystemStatus();

            return response()->json([
                'success' => true,
                'data' => [
                    'installation' => $installationInfo,
                    'statistics' => $statistics,
                    'plugins' => $plugins,
                    'system_status' => $systemStatus,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Installation API Info Error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving installation information'
            ], 500);
        }
    }

    /**
     * Health check voor deze installatie
     */
    public function health(): JsonResponse
    {
        try {
            $checks = [];
            $overallStatus = 'healthy';

            // Database check
            try {
                DB::connection()->getPdo();
                $checks['database'] = [
                    'status' => 'healthy',
                    'message' => 'Database connection successful'
                ];
            } catch (\Exception $e) {
                $checks['database'] = [
                    'status' => 'unhealthy',
                    'message' => 'Database connection failed: ' . $e->getMessage()
                ];
                $overallStatus = 'unhealthy';
            }

            // Storage check
            $storageWritable = is_writable(storage_path());
            $checks['storage'] = [
                'status' => $storageWritable ? 'healthy' : 'unhealthy',
                'message' => $storageWritable ? 'Storage directory writable' : 'Storage directory not writable'
            ];
            if (!$storageWritable) {
                $overallStatus = 'unhealthy';
            }

            // Cache check
            try {
                cache()->put('health_check', 'test', 10);
                $cacheTest = cache()->get('health_check') === 'test';
                cache()->forget('health_check');
                
                $checks['cache'] = [
                    'status' => $cacheTest ? 'healthy' : 'unhealthy',
                    'message' => $cacheTest ? 'Cache working' : 'Cache not working'
                ];
                if (!$cacheTest) {
                    $overallStatus = 'unhealthy';
                }
            } catch (\Exception $e) {
                $checks['cache'] = [
                    'status' => 'unhealthy',
                    'message' => 'Cache error: ' . $e->getMessage()
                ];
                $overallStatus = 'unhealthy';
            }

            // Plugins check - plugins are no longer used
            $checks['plugins'] = [
                'status' => 'healthy',
                'message' => "Plugin system removed",
                'active_plugins' => 0,
                'total_plugins' => 0,
            ];

            // Disk space check
            $freeSpace = disk_free_space(storage_path());
            $totalSpace = disk_total_space(storage_path());
            $freeSpacePercent = ($freeSpace / $totalSpace) * 100;
            
            $diskStatus = 'healthy';
            $diskMessage = 'Sufficient disk space';
            if ($freeSpacePercent < 10) {
                $diskStatus = 'warning';
                $diskMessage = 'Low disk space';
            }
            if ($freeSpacePercent < 5) {
                $diskStatus = 'unhealthy';
                $diskMessage = 'Critical disk space';
                $overallStatus = 'unhealthy';
            }

            $checks['disk_space'] = [
                'status' => $diskStatus,
                'message' => $diskMessage,
                'free_space_mb' => round($freeSpace / 1024 / 1024),
                'total_space_mb' => round($totalSpace / 1024 / 1024),
                'free_percentage' => round($freeSpacePercent, 2),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'overall_status' => $overallStatus,
                    'timestamp' => now()->toISOString(),
                    'checks' => $checks,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Installation API Health Error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error checking installation health',
                'data' => [
                    'overall_status' => 'error',
                    'timestamp' => now()->toISOString(),
                ]
            ], 500);
        }
    }

    /**
     * Get system status informatie
     */
    private function getSystemStatus(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'database_type' => config('database.default'),
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'queue_driver' => config('queue.default'),
            'mail_driver' => config('mail.default'),
        ];
    }
}