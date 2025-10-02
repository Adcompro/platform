<?php

namespace App\Http\Controllers;

use App\Services\MicrosoftGraphService;
use Dcblogdev\MsGraph\Facades\MsGraph;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use Exception;

class MsGraphAuthController extends Controller
{
    /**
     * Connect/OAuth handler
     * This single method handles both the initial connect and the OAuth callback
     */
    public function connect(Request $request)
    {
        try {
            // Check if this is a callback (has code parameter)
            if ($request->has('code')) {
                // This is the OAuth callback - handle token exchange manually
                $tokenResponse = $this->exchangeCodeForTokens($request->get('code'));

                if ($tokenResponse) {
                    // Get account info
                    $account = $this->getAccountInfo($tokenResponse['access_token']);

                    // Store tokens persistently
                    MicrosoftGraphService::storeTokens($tokenResponse, $account);

                    return redirect()->route('calendar.index')
                        ->with('success', 'Successfully connected to Microsoft 365 as ' . ($account['email'] ?? 'your account') . '!');
                }

                return redirect()->route('calendar.index')
                    ->with('error', 'Failed to complete Microsoft 365 authentication.');
            } else {
                // This is the initial connect request
                // Check if user already has valid tokens
                if (MicrosoftGraphService::hasValidTokens()) {
                    return redirect()->route('calendar.index')
                        ->with('success', 'Already connected to Microsoft 365!');
                }

                // Check if a specific account is suggested
                if ($request->has('login_hint')) {
                    Session::put('suggested_ms_email', $request->get('login_hint'));
                }

                // Always force account selection for multiple account support
                return MicrosoftGraphService::connectWithAccountSelection();
            }
        } catch (Exception $e) {
            return redirect()->route('calendar.index')
                ->with('error', 'Failed to connect to Microsoft 365: ' . $e->getMessage());
        }
    }

    /**
     * Exchange authorization code for access tokens
     */
    private function exchangeCodeForTokens($code)
    {
        try {
            $response = Http::asForm()->post(config('msgraph.urlAccessToken'), [
                'client_id' => config('msgraph.clientId'),
                'client_secret' => config('msgraph.clientSecret'),
                'code' => $code,
                'redirect_uri' => config('msgraph.redirectUri'),
                'grant_type' => 'authorization_code',
                'scope' => config('msgraph.scopes'),
            ]);

            if ($response->successful()) {
                return $response->json();
            }
        } catch (Exception $e) {
            // Token exchange failed
        }

        return null;
    }

    /**
     * Get account information using access token
     */
    private function getAccountInfo($accessToken)
    {
        try {
            $response = Http::withToken($accessToken)
                ->get('https://graph.microsoft.com/v1.0/me');

            if ($response->successful()) {
                $user = $response->json();
                return [
                    'email' => $user['mail'] ?? $user['userPrincipalName'] ?? 'Unknown',
                    'name' => $user['displayName'] ?? 'Unknown User',
                    'id' => $user['id'] ?? null
                ];
            }
        } catch (Exception $e) {
            // Account info fetch failed
        }

        return [
            'email' => 'Unknown',
            'name' => 'Unknown User',
            'id' => null
        ];
    }
    
    /**
     * Disconnect from Microsoft Graph
     */
    public function disconnect()
    {
        MicrosoftGraphService::disconnect();

        return redirect()->route('calendar.index')
            ->with('success', 'Disconnected from Microsoft 365.');
    }
}