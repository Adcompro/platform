<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TeamleaderService
{
    // Teamleader Focus API base URL
    private const API_BASE_URL = 'https://api.focus.teamleader.eu';
    private const AUTH_URL = 'https://focus.teamleader.eu/oauth2/authorize';
    private const TOKEN_URL = 'https://focus.teamleader.eu/oauth2/access_token';

    /**
     * Get authorization URL voor OAuth flow
     */
    public static function getAuthorizationUrl(string $state): string
    {
        $clientId = Setting::get('teamleader_client_id');
        $redirectUri = Setting::get('teamleader_redirect_uri');

        $params = http_build_query([
            'client_id' => $clientId,
            'response_type' => 'code',
            'redirect_uri' => $redirectUri,
            'state' => $state,
        ]);

        return self::AUTH_URL . '?' . $params;
    }

    /**
     * Exchange authorization code voor access token
     */
    public static function exchangeCodeForToken(string $code): array
    {
        $clientId = Setting::get('teamleader_client_id');
        $clientSecret = Setting::get('teamleader_client_secret');
        $redirectUri = Setting::get('teamleader_redirect_uri');

        try {
            $response = Http::asForm()->post(self::TOKEN_URL, [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'code' => $code,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $redirectUri,
            ]);

            if ($response->failed()) {
                Log::error('Teamleader token exchange failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                throw new \Exception('Failed to exchange code for token: ' . $response->body());
            }

            $data = $response->json();

            // Sla tokens op in settings
            Setting::set('teamleader_access_token', $data['access_token']);
            Setting::set('teamleader_refresh_token', $data['refresh_token']);
            Setting::set('teamleader_token_expires_at', Carbon::now()->addSeconds($data['expires_in'])->toDateTimeString());

            Log::info('Teamleader access token obtained successfully');

            return $data;

        } catch (\Exception $e) {
            Log::error('Error exchanging Teamleader code for token', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Refresh access token met refresh token
     */
    public static function refreshAccessToken(): bool
    {
        $clientId = Setting::get('teamleader_client_id');
        $clientSecret = Setting::get('teamleader_client_secret');
        $refreshToken = Setting::get('teamleader_refresh_token');

        if (!$refreshToken) {
            Log::warning('No refresh token available');
            return false;
        }

        try {
            $response = Http::asForm()->post(self::TOKEN_URL, [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'refresh_token' => $refreshToken,
                'grant_type' => 'refresh_token',
            ]);

            if ($response->failed()) {
                Log::error('Teamleader token refresh failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return false;
            }

            $data = $response->json();

            // Update tokens
            Setting::set('teamleader_access_token', $data['access_token']);
            Setting::set('teamleader_refresh_token', $data['refresh_token']);
            Setting::set('teamleader_token_expires_at', Carbon::now()->addSeconds($data['expires_in'])->toDateTimeString());

            Log::info('Teamleader access token refreshed successfully');

            return true;

        } catch (\Exception $e) {
            Log::error('Error refreshing Teamleader token', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Check if token is valid (niet verlopen)
     */
    public static function isTokenValid(): bool
    {
        $accessToken = Setting::get('teamleader_access_token');
        $expiresAt = Setting::get('teamleader_token_expires_at');

        if (!$accessToken || !$expiresAt) {
            return false;
        }

        // Check of token nog minimaal 5 minuten geldig is
        return Carbon::parse($expiresAt)->subMinutes(5)->isFuture();
    }

    /**
     * Get valid access token (refresht automatisch als nodig)
     */
    public static function getAccessToken(): ?string
    {
        if (!self::isTokenValid()) {
            // Probeer token te refreshen
            if (!self::refreshAccessToken()) {
                Log::warning('Failed to refresh Teamleader token, re-authorization needed');
                return null;
            }
        }

        return Setting::get('teamleader_access_token');
    }

    /**
     * Maak een API call naar Teamleader Focus
     */
    public static function apiCall(string $endpoint, array $data = [], string $method = 'POST'): array
    {
        $accessToken = self::getAccessToken();

        if (!$accessToken) {
            throw new \Exception('No valid access token available. Please authorize first.');
        }

        try {
            $url = self::API_BASE_URL . $endpoint;

            $response = Http::withToken($accessToken)
                ->accept('application/json')
                ->contentType('application/json')
                ->send($method, $url, [
                    'json' => $data
                ]);

            if ($response->failed()) {
                Log::error('Teamleader API call failed', [
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                throw new \Exception('API call failed: ' . $response->body());
            }

            return $response->json();

        } catch (\Exception $e) {
            Log::error('Error making Teamleader API call', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * List companies from Teamleader
     */
    public static function listCompanies(int $page = 1, int $pageSize = 100): array
    {
        return self::apiCall('/companies.list', [
            'page' => [
                'size' => $pageSize,
                'number' => $page
            ]
        ]);
    }

    /**
     * Get company info by ID
     */
    public static function getCompany(string $companyId): array
    {
        return self::apiCall('/companies.info', [
            'id' => $companyId
        ]);
    }

    /**
     * List contacts from Teamleader
     */
    public static function listContacts(int $page = 1, int $pageSize = 100): array
    {
        return self::apiCall('/contacts.list', [
            'page' => [
                'size' => $pageSize,
                'number' => $page
            ]
        ]);
    }

    /**
     * Get contact info by ID
     */
    public static function getContact(string $contactId): array
    {
        return self::apiCall('/contacts.info', [
            'id' => $contactId
        ]);
    }

    /**
     * List contacts for a specific company
     */
    public static function listContactsForCompany(string $companyId, int $page = 1, int $pageSize = 100): array
    {
        return self::apiCall('/contacts.list', [
            'filter' => [
                'company_id' => $companyId  // ✅ CORRECT - volgens API docs
            ],
            'page' => [
                'size' => $pageSize,
                'number' => $page
            ]
        ]);
    }

    /**
     * List projects from Teamleader
     */
    public static function listProjects(int $page = 1, int $pageSize = 100, ?string $companyId = null): array
    {
        $params = [
            'page' => [
                'size' => $pageSize,
                'number' => $page
            ]
        ];

        // Voeg company filter toe als opgegeven
        if ($companyId) {
            $params['filter'] = [
                'company_id' => $companyId
            ];
        }

        return self::apiCall('/projects.list', $params);
    }

    /**
     * Get project info by ID
     */
    public static function getProject(string $projectId): array
    {
        return self::apiCall('/projects.info', [
            'id' => $projectId
        ]);
    }

    /**
     * List projects for a specific company/customer
     */
    public static function listProjectsForCompany(string $companyId, int $page = 1, int $pageSize = 100): array
    {
        return self::listProjects($page, $pageSize, $companyId);
    }

    /**
     * List time tracking entries from Teamleader
     */
    public static function listTimeTracking(int $page = 1, int $pageSize = 100, array $filters = []): array
    {
        $params = [
            'page' => [
                'size' => $pageSize,
                'number' => $page
            ]
        ];

        if (!empty($filters)) {
            $params['filter'] = $filters;
        }

        return self::apiCall('/timeTracking.list', $params);
    }

    /**
     * Get time tracking info by ID
     */
    public static function getTimeTracking(string $timeTrackingId): array
    {
        return self::apiCall('/timeTracking.info', [
            'id' => $timeTrackingId
        ]);
    }

    /**
     * Get time tracking entries for a specific project
     */
    public static function getProjectTimeTracking(string $projectId, int $page = 1, int $pageSize = 100): array
    {
        return self::apiCall('/timeTracking.list', [
            'filter' => [
                'project_id' => $projectId
            ],
            'page' => [
                'size' => $pageSize,
                'number' => $page
            ]
        ]);
    }

    /**
     * Create a new time tracking entry
     */
    public static function createTimeTracking(array $data): array
    {
        return self::apiCall('/timeTracking.add', $data);
    }

    /**
     * List invoices from Teamleader
     */
    public static function listInvoices(int $page = 1, int $pageSize = 100): array
    {
        return self::apiCall('/invoices.list', [
            'page' => [
                'size' => $pageSize,
                'number' => $page
            ]
        ]);
    }

    /**
     * Get invoice info by ID
     */
    public static function getInvoice(string $invoiceId): array
    {
        return self::apiCall('/invoices.info', [
            'id' => $invoiceId
        ]);
    }

    /**
     * List users from Teamleader
     */
    public static function listUsers(): array
    {
        return self::apiCall('/users.list', []);
    }

    /**
     * Get current user info
     */
    public static function getCurrentUser(): array
    {
        return self::apiCall('/users.me', []);
    }

    /**
     * Test API connection
     */
    public static function testConnection(): array
    {
        try {
            $user = self::getCurrentUser();
            return [
                'success' => true,
                'message' => 'Connected to Teamleader Focus successfully',
                'user' => $user
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * List milestones for a specific project
     */
    public static function listMilestonesForProject(string $projectId, int $page = 1, int $pageSize = 100): array
    {
        return self::apiCall('/milestones.list', [
            'filter' => [
                'project_id' => $projectId
            ],
            'page' => [
                'size' => $pageSize,
                'number' => $page
            ]
        ]);
    }

    /**
     * Get milestone info by ID
     */
    public static function getMilestone(string $milestoneId): array
    {
        return self::apiCall('/milestones.info', [
            'id' => $milestoneId
        ]);
    }

    /**
     * List tasks for a specific milestone
     */
    public static function listTasksForMilestone(string $milestoneId, int $page = 1, int $pageSize = 100): array
    {
        return self::apiCall('/tasks.list', [
            'filter' => [
                'milestone_id' => $milestoneId
            ],
            'page' => [
                'size' => $pageSize,
                'number' => $page
            ]
        ]);
    }

    /**
     * List tasks for a specific project
     */
    public static function listTasksForProject(string $projectId, int $page = 1, int $pageSize = 100): array
    {
        return self::apiCall('/tasks.list', [
            'filter' => [
                'project_id' => $projectId
            ],
            'page' => [
                'size' => $pageSize,
                'number' => $page
            ]
        ]);
    }

    /**
     * Get task info by ID
     */
    public static function getTask(string $taskId): array
    {
        return self::apiCall('/tasks.info', [
            'id' => $taskId
        ]);
    }
}
