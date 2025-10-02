<?php

namespace App\Services\Social;

use App\Models\SocialMediaSource;
use App\Models\SocialMediaMention;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LinkedInService
{
    protected $accessToken;
    protected $clientId;
    protected $clientSecret;
    protected $source;

    public function __construct(SocialMediaSource $source)
    {
        $this->source = $source;
        $credentials = $source->api_credentials;
        
        if ($credentials) {
            // Decrypt credentials if they're encrypted
            $this->accessToken = isset($credentials['access_token']) ? 
                (str_starts_with($credentials['access_token'], 'eyJ') ? 
                    \Illuminate\Support\Facades\Crypt::decryptString($credentials['access_token']) : 
                    $credentials['access_token']) : null;
                    
            $this->clientId = isset($credentials['client_id']) ? 
                (str_starts_with($credentials['client_id'], 'eyJ') ? 
                    \Illuminate\Support\Facades\Crypt::decryptString($credentials['client_id']) : 
                    $credentials['client_id']) : null;
                    
            $this->clientSecret = isset($credentials['client_secret']) ? 
                (str_starts_with($credentials['client_secret'], 'eyJ') ? 
                    \Illuminate\Support\Facades\Crypt::decryptString($credentials['client_secret']) : 
                    $credentials['client_secret']) : null;
        }
    }

    /**
     * Search for company posts and mentions
     * Note: LinkedIn API has limited search capabilities for public content
     */
    public function searchPosts(array $keywords): array
    {
        if (!$this->accessToken) {
            Log::warning('LinkedIn API: No access token configured');
            return [];
        }

        // LinkedIn doesn't offer public post search like Twitter
        // This would need to be implemented using organization shares API
        // for monitoring your own company's posts and their engagement
        
        try {
            // Get organization posts
            $orgId = $this->source->account_id;
            if (!$orgId) {
                return [];
            }
            
            $response = Http::withToken($this->accessToken)
                ->get("https://api.linkedin.com/v2/organizationalEntityShareStatistics", [
                    'q' => 'organizationalEntity',
                    'organizationalEntity' => "urn:li:organization:{$orgId}",
                    'count' => 50
                ]);
            
            if ($response->successful()) {
                return $this->processLinkedInPosts($response->json(), $keywords);
            }
        } catch (\Exception $e) {
            Log::error('LinkedIn search failed', [
                'error' => $e->getMessage()
            ]);
        }
        
        return [];
    }

    /**
     * Get organization share statistics
     */
    public function getOrganizationShares(string $organizationId): array
    {
        if (!$this->accessToken) {
            return [];
        }

        try {
            // Get shares/posts from organization
            $response = Http::withToken($this->accessToken)
                ->withHeaders([
                    'X-Restli-Protocol-Version' => '2.0.0'
                ])
                ->get("https://api.linkedin.com/v2/shares", [
                    'q' => 'owners',
                    'owners' => "urn:li:organization:{$organizationId}",
                    'count' => 100,
                    'sortBy' => 'CREATED_DESC'
                ]);
            
            if ($response->successful()) {
                return $this->processShares($response->json());
            }
        } catch (\Exception $e) {
            Log::error('LinkedIn shares fetch failed', [
                'org_id' => $organizationId,
                'error' => $e->getMessage()
            ]);
        }
        
        return [];
    }

    /**
     * Process LinkedIn shares/posts
     */
    protected function processShares(array $data): array
    {
        $mentions = [];
        
        if (!isset($data['elements'])) {
            return $mentions;
        }
        
        foreach ($data['elements'] as $share) {
            $mentions[] = $this->createMentionFromShare($share);
        }
        
        return $mentions;
    }

    /**
     * Create mention from LinkedIn share
     */
    protected function createMentionFromShare(array $share): SocialMediaMention
    {
        $text = $share['text']['text'] ?? $share['commentary'] ?? '';
        $shareId = $share['id'] ?? uniqid('linkedin_');
        $activity = $share['activity'] ?? null;
        
        // Extract hashtags from text
        preg_match_all('/#(\w+)/', $text, $matches);
        $hashtags = $matches[1] ?? [];
        
        // Extract mentions from text
        preg_match_all('/@(\w+)/', $text, $matches);
        $mentions = $matches[1] ?? [];
        
        // Extract URLs
        $urls = [];
        if (isset($share['content']['contentEntities'])) {
            foreach ($share['content']['contentEntities'] as $entity) {
                if (isset($entity['entityLocation'])) {
                    $urls[] = $entity['entityLocation'];
                }
            }
        }
        
        // Get share statistics if available
        $stats = $share['socialMetadata'] ?? [];
        
        $mention = SocialMediaMention::updateOrCreate(
            ['platform_post_id' => $shareId],
            [
                'source_id' => $this->source->id,
                'author_name' => $share['owner']['name'] ?? $this->source->account_name,
                'author_handle' => $this->source->account_name,
                'author_profile_url' => "https://www.linkedin.com/company/{$this->source->account_name}",
                'author_followers' => null, // Would need separate API call
                'author_verified' => false, // LinkedIn doesn't have verification badges
                'content' => $text,
                'hashtags' => $hashtags,
                'mentions' => $mentions,
                'urls' => $urls,
                'media_urls' => $this->extractMediaUrls($share),
                'post_url' => $activity ? "https://www.linkedin.com/feed/update/{$activity}" : null,
                'published_at' => isset($share['created']['time']) 
                    ? \Carbon\Carbon::createFromTimestampMs($share['created']['time']) 
                    : now(),
                'likes_count' => $stats['likeCount'] ?? 0,
                'shares_count' => $stats['shareCount'] ?? 0,
                'comments_count' => $stats['commentCount'] ?? 0,
                'views_count' => $stats['impressionCount'] ?? null,
                'post_type' => 'post',
                'is_repost' => isset($share['resharedShare']),
                'raw_data' => $share
            ]
        );
        
        // Calculate engagement rate
        $mention->update([
            'engagement_rate' => $mention->calculateEngagementRate()
        ]);
        
        return $mention;
    }

    /**
     * Extract media URLs from share
     */
    protected function extractMediaUrls(array $share): array
    {
        $mediaUrls = [];
        
        if (isset($share['content']['contentEntities'])) {
            foreach ($share['content']['contentEntities'] as $entity) {
                if (isset($entity['thumbnails'])) {
                    foreach ($entity['thumbnails'] as $thumbnail) {
                        if (isset($thumbnail['resolvedUrl'])) {
                            $mediaUrls[] = $thumbnail['resolvedUrl'];
                        }
                    }
                }
            }
        }
        
        return $mediaUrls;
    }

    /**
     * Process LinkedIn posts with keyword filtering
     */
    protected function processLinkedInPosts(array $data, array $keywords): array
    {
        $mentions = [];
        
        // This is a simplified implementation
        // Real implementation would need proper LinkedIn API integration
        
        return $mentions;
    }

    /**
     * Refresh access token using refresh token
     */
    public function refreshAccessToken(): bool
    {
        if (!$this->clientId || !$this->clientSecret) {
            return false;
        }

        $refreshToken = $this->source->api_credentials['refresh_token'] ?? null;
        if (!$refreshToken) {
            return false;
        }

        try {
            $response = Http::asForm()->post('https://www.linkedin.com/oauth/v2/accessToken', [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                $credentials = $this->source->api_credentials;
                $credentials['access_token'] = $data['access_token'];
                
                $this->source->update([
                    'api_credentials' => $credentials
                ]);
                
                $this->accessToken = $data['access_token'];
                return true;
            }
        } catch (\Exception $e) {
            Log::error('LinkedIn token refresh failed', [
                'error' => $e->getMessage()
            ]);
        }
        
        return false;
    }

    /**
     * Check if service is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->accessToken);
    }

    /**
     * Test connection
     */
    public function testConnection(): bool
    {
        if (!$this->accessToken) {
            return false;
        }

        try {
            $response = Http::withToken($this->accessToken)
                ->get('https://api.linkedin.com/v2/me');
            
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}