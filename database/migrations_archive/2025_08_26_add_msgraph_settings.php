<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Setting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Microsoft Graph/Azure AD settings
        $msGraphSettings = [
            'msgraph_client_id' => env('MSGRAPH_CLIENT_ID', ''),
            'msgraph_client_secret' => env('MSGRAPH_CLIENT_SECRET', ''),
            'msgraph_tenant_id' => env('MSGRAPH_TENANT_ID', 'common'),
            'msgraph_redirect_uri' => env('MSGRAPH_REDIRECT_URI', 'https://progress.adcompro.app/msgraph/oauth'),
            'msgraph_landing_url' => env('MSGRAPH_LANDING_URL', '/calendar'),
            'msgraph_allow_login' => env('MSGRAPH_ALLOW_LOGIN', 'true'),
            'msgraph_allow_access_token_routes' => env('MSGRAPH_ALLOW_ACCESS_TOKEN_ROUTES', 'true'),
        ];

        // Insert settings into database if they don't exist
        foreach ($msGraphSettings as $key => $value) {
            Setting::firstOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'type' => in_array($key, ['msgraph_allow_login', 'msgraph_allow_access_token_routes']) ? 'boolean' : 'string'
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove Microsoft Graph settings
        Setting::whereIn('key', [
            'msgraph_client_id',
            'msgraph_client_secret',
            'msgraph_tenant_id',
            'msgraph_redirect_uri',
            'msgraph_landing_url',
            'msgraph_allow_login',
            'msgraph_allow_access_token_routes',
        ])->delete();
    }
};