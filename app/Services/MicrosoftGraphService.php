<?php

namespace App\Services;

use App\Models\UserMsGraphToken;
use Dcblogdev\MsGraph\Facades\MsGraph;
use Dcblogdev\MsGraph\Models\MsGraphToken;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Exception;

class MicrosoftGraphService
{
    /**
     * Get the OAuth URL with forced account selection
     */
    public static function getConnectUrl($forceAccountSelection = true)
    {
        $params = [
            'client_id' => config('msgraph.clientId'),
            'response_type' => 'code',
            'redirect_uri' => config('msgraph.redirectUri'),
            'response_mode' => 'query',
            'scope' => config('msgraph.scopes'),
        ];

        // Force account selection to prevent auto-login with cached account
        if ($forceAccountSelection) {
            $params['prompt'] = 'select_account';
        }

        // Add domain hint if we want to suggest a specific domain
        $suggestedEmail = Session::get('suggested_ms_email');
        if ($suggestedEmail) {
            $params['login_hint'] = $suggestedEmail;
            // Extract domain for domain_hint
            $domain = substr(strrchr($suggestedEmail, "@"), 1);
            if ($domain) {
                $params['domain_hint'] = $domain;
            }
        }

        $url = config('msgraph.urlAuthorize') . '?' . http_build_query($params);

        return $url;
    }

    /**
     * Connect with forced account selection
     */
    public static function connectWithAccountSelection()
    {
        $url = self::getConnectUrl(true);
        return redirect()->away($url);
    }

    /**
     * Initialize connection using stored user token
     */
    public static function initializeConnection()
    {
        if (!Auth::check()) {
            return false;
        }

        $userToken = UserMsGraphToken::getForCurrentUser();
        if (!$userToken) {
            return false;
        }

        // Check if token needs refresh
        if ($userToken->willExpireSoon() && $userToken->refresh_token) {
            $refreshed = self::refreshToken($userToken);
            if (!$refreshed) {
                return false;
            }
            $userToken = $userToken->fresh();
        }

        // If token is still expired, authentication failed
        if ($userToken->isExpired()) {
            $userToken->delete();
            return false;
        }

        // Set the token in the MsGraph package by manipulating its token storage
        self::setMsGraphToken($userToken);

        return true;
    }

    /**
     * Store tokens after successful authentication
     */
    public static function storeTokens($tokenData, $accountInfo)
    {
        if (!Auth::check()) {
            return false;
        }

        // Store in our custom table
        $userToken = UserMsGraphToken::storeForCurrentUser($tokenData, $accountInfo);

        // Also store in session for immediate use
        Session::put('ms_user_email', $accountInfo['email']);

        return $userToken;
    }

    /**
     * Check if user has valid stored tokens
     */
    public static function hasValidTokens()
    {
        if (!Auth::check()) {
            return false;
        }

        $userToken = UserMsGraphToken::getForCurrentUser();
        return $userToken && !$userToken->isExpired();
    }

    /**
     * Refresh an expired token
     */
    public static function refreshToken(UserMsGraphToken $userToken)
    {
        if (!$userToken->refresh_token) {
            return false;
        }

        try {
            $response = Http::asForm()->post(config('msgraph.urlAccessToken'), [
                'client_id' => config('msgraph.clientId'),
                'client_secret' => config('msgraph.clientSecret'),
                'grant_type' => 'refresh_token',
                'refresh_token' => $userToken->refresh_token,
                'scope' => config('msgraph.scopes'),
            ]);

            if ($response->successful()) {
                $tokenData = $response->json();

                $userToken->update([
                    'access_token' => $tokenData['access_token'],
                    'refresh_token' => $tokenData['refresh_token'] ?? $userToken->refresh_token,
                    'expires_at' => Carbon::now()->addSeconds($tokenData['expires_in'])
                ]);

                return true;
            }
        } catch (Exception $e) {
            // Refresh failed - token is invalid
        }

        return false;
    }

    /**
     * Set token in MsGraph package
     */
    private static function setMsGraphToken(UserMsGraphToken $userToken)
    {
        // Create or update the MsGraph package token
        MsGraphToken::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'email' => $userToken->email,
                'access_token' => $userToken->access_token,
                'refresh_token' => $userToken->refresh_token,
                'expires' => $userToken->expires_at->timestamp,
            ]
        );
    }

    /**
     * Get current connected account info
     */
    public static function getCurrentAccount()
    {
        // First try to initialize connection with stored tokens
        if (!MsGraph::isConnected()) {
            self::initializeConnection();
        }

        if (!MsGraph::isConnected()) {
            return null;
        }

        try {
            $user = MsGraph::get('me');
            return [
                'email' => $user['mail'] ?? $user['userPrincipalName'] ?? 'Unknown',
                'name' => $user['displayName'] ?? 'Unknown User',
                'id' => $user['id'] ?? null
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check if the connected account has a valid mailbox
     */
    public static function hasValidMailbox()
    {
        // First try to initialize connection with stored tokens
        if (!MsGraph::isConnected()) {
            self::initializeConnection();
        }

        if (!MsGraph::isConnected()) {
            return false;
        }

        try {
            // Try to access calendar - if it fails, no valid mailbox
            MsGraph::get('me/calendar');
            return true;
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'MailboxNotEnabledForRESTAPI') !== false) {
                return false;
            }
            // Other errors might be temporary
            return true;
        }
    }

    /**
     * Disconnect and clear all Microsoft tokens
     */
    public static function disconnect()
    {
        // Clear standard MsGraph tokens
        MsGraph::disconnect();

        // Clear our custom user tokens
        if (Auth::check()) {
            UserMsGraphToken::where('user_id', Auth::id())->delete();
        }

        // Clear session data
        Session::forget('ms_user_email');
        Session::forget('suggested_ms_email');
    }
}