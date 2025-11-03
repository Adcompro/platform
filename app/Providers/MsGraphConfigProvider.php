<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Setting;
use Illuminate\Support\Facades\Config;

class MsGraphConfigProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services - Load MS Graph config from database
     */
    public function boot(): void
    {
        // Only load if we're not running migrations (to avoid issues during fresh install)
        if (!app()->runningInConsole() || !$this->isMigrationCommand()) {
            try {
                // Check if settings table exists
                if (\Schema::hasTable('settings')) {
                    // Load MS Graph settings from database
                    $msGraphSettings = [
                        'msgraph.clientId' => Setting::get('msgraph_client_id', env('MSGRAPH_CLIENT_ID', '')),
                        'msgraph.clientSecret' => Setting::get('msgraph_client_secret', env('MSGRAPH_CLIENT_SECRET', '')),
                        'msgraph.redirectUri' => Setting::get('msgraph_redirect_uri', env('MSGRAPH_REDIRECT_URI', '')),
                        'msgraph.tenantId' => Setting::get('msgraph_tenant_id', env('MSGRAPH_TENANT_ID', 'common')),
                        'msgraph.urlAccessToken' => 'https://login.microsoftonline.com/' . Setting::get('msgraph_tenant_id', 'common') . '/oauth2/v2.0/token',
                        'msgraph.urlAuthorize' => 'https://login.microsoftonline.com/' . Setting::get('msgraph_tenant_id', 'common') . '/oauth2/v2.0/authorize',
                        'msgraph.scope' => 'https://graph.microsoft.com/.default',
                        'msgraph.landingUrl' => Setting::get('msgraph_landing_url', env('MSGRAPH_LANDING_URL', '/calendar')),
                        'msgraph.allowLogin' => Setting::get('msgraph_allow_login', env('MSGRAPH_ALLOW_LOGIN', 'true')) === 'true',
                        'msgraph.allowAccessTokenRoutes' => Setting::get('msgraph_allow_access_token_routes', env('MSGRAPH_ALLOW_ACCESS_TOKEN_ROUTES', 'true')) === 'true',
                    ];
                    
                    // Set all config values
                    foreach ($msGraphSettings as $key => $value) {
                        Config::set($key, $value);
                    }
                    
                    // Also update the OAuth URLs based on tenant ID
                    $tenantId = Setting::get('msgraph_tenant_id', 'common');
                    Config::set('msgraph.urlAccessToken', 'https://login.microsoftonline.com/' . $tenantId . '/oauth2/v2.0/token');
                    Config::set('msgraph.urlAuthorize', 'https://login.microsoftonline.com/' . $tenantId . '/oauth2/v2.0/authorize');
                }
            } catch (\Exception $e) {
                // If database is not available yet, fall back to env values
                // This can happen during migrations or fresh installs
                \Log::debug('MsGraphConfigProvider: Could not load settings from database, using env values. ' . $e->getMessage());
            }
        }
    }
    
    /**
     * Check if we're running a migration command
     */
    private function isMigrationCommand(): bool
    {
        $argv = $_SERVER['argv'] ?? [];
        
        foreach ($argv as $arg) {
            if (strpos($arg, 'migrate') !== false) {
                return true;
            }
        }
        
        return false;
    }
}