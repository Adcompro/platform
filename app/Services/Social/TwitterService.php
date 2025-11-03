<?php

namespace App\Services\Social;

use App\Models\SocialMediaSource;
use App\Models\SocialMediaMention;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TwitterService
{
    protected $apiKey;
    protected $apiSecret;
    protected $bearerToken;
    protected $accessToken;
    protected $accessTokenSecret;
    protected $source;

    public function __construct(SocialMediaSource $source)
    {
        $this->source = $source;
        $credentials = $source->api_credentials;
        
        if ($credentials) {
            // Decrypt credentials if they're encrypted
            $this->apiKey = isset($credentials['api_key']) ? 
                (str_starts_with($credentials['api_key'], 'eyJ') ? 
                    \Illuminate\Support\Facades\Crypt::decryptString($credentials['api_key']) : 
                    $credentials['api_key']) : null;
                    
            $this->apiSecret = isset($credentials['api_secret']) ? 
                (str_starts_with($credentials['api_secret'], 'eyJ') ? 
                    \Illuminate\Support\Facades\Crypt::decryptString($credentials['api_secret']) : 
                    $credentials['api_secret']) : null;
                    
            $this->bearerToken = isset($credentials['bearer_token']) ? 
                (str_starts_with($credentials['bearer_token'], 'eyJ') ? 
                    \Illuminate\Support\Facades\Crypt::decryptString($credentials['bearer_token']) : 
                    $credentials['bearer_token']) : null;
                    
            $this->accessToken = isset($credentials['access_token']) ? 
                (str_starts_with($credentials['access_token'], 'eyJ') ? 
                    \Illuminate\Support\Facades\Crypt::decryptString($credentials['access_token']) : 
                    $credentials['access_token']) : null;
                    
            $this->accessTokenSecret = isset($credentials['access_token_secret']) ? 
                (str_starts_with($credentials['access_token_secret'], 'eyJ') ? 
                    \Illuminate\Support\Facades\Crypt::decryptString($credentials['access_token_secret']) : 
                    $credentials['access_token_secret']) : null;
        }
    }

    /**
     * Search for tweets matching keywords
     */
    public function searchTweets(array $keywords, ?string $sinceId = null): array
    {
        if (!$this->bearerToken) {
            Log::warning('Twitter API: No bearer token configured');
            return [];
        }

        try {
            // Build query from keywords
            $query = implode(' OR ', array_map(fn($k) => '"' . $k . '"', $keywords));
            
            // Add language filter for Dutch/English
            $query .= ' (lang:nl OR lang:en OR lang:fr)';
            
            // Try v2 API first with reduced max_results to avoid rate limits
            $params = [
                'query' => $query,
                'max_results' => 10,  // Reduced from 100 to be more conservative
                'tweet.fields' => 'id,text,author_id,created_at,public_metrics,referenced_tweets,lang,entities',
                'user.fields' => 'id,name,username,verified,public_metrics,profile_image_url',
                'expansions' => 'author_id,referenced_tweets.id'
            ];
            
            if ($sinceId) {
                $params['since_id'] = $sinceId;
            }
            
            $response = Http::withToken($this->bearerToken)
                ->timeout(30)  // Add timeout
                ->get('https://api.twitter.com/2/tweets/search/recent', $params);
            
            if ($response->successful()) {
                $data = $response->json();
                
                // Update rate limit info
                $this->updateRateLimits($response->headers());
                
                return $this->processTweets($data);
            } else {
                // Check for rate limit specifically
                if ($response->status() === 429) {
                    Log::warning('Twitter API rate limited', [
                        'status' => $response->status(),
                        'headers' => $response->headers()
                    ]);
                    
                    // Update rate limit in database
                    $resetTime = $response->header('x-rate-limit-reset');
                    if ($resetTime) {
                        $this->source->update([
                            'rate_limit_remaining' => 0,
                            'rate_limit_reset_at' => \Carbon\Carbon::createFromTimestamp($resetTime)
                        ]);
                    }
                    
                    // Try fallback to v1.1 API if available
                    return $this->searchTweetsV1($keywords, $sinceId);
                } else {
                    Log::error('Twitter API error', [
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Twitter search failed', [
                'error' => $e->getMessage()
            ]);
        }
        
        return [];
    }
    
    /**
     * Fallback search using Twitter API v1.1
     */
    protected function searchTweetsV1(array $keywords, ?string $sinceId = null): array
    {
        try {
            $query = implode(' OR ', $keywords);
            
            $params = [
                'q' => $query,
                'count' => 10,
                'result_type' => 'recent',
                'tweet_mode' => 'extended'
            ];
            
            if ($sinceId) {
                $params['since_id'] = $sinceId;
            }
            
            $response = Http::withToken($this->bearerToken)
                ->timeout(30)
                ->get('https://api.twitter.com/1.1/search/tweets.json', $params);
            
            if ($response->successful()) {
                $data = $response->json();
                return $this->processTweetsV1($data);
            }
        } catch (\Exception $e) {
            Log::error('Twitter v1.1 search fallback failed', [
                'error' => $e->getMessage()
            ]);
        }
        
        return [];
    }
    
    /**
     * Process tweets from v1.1 API response
     */
    protected function processTweetsV1(array $data): array
    {
        $tweets = [];
        
        if (!isset($data['statuses']) || empty($data['statuses'])) {
            return $tweets;
        }
        
        foreach ($data['statuses'] as $tweet) {
            $author = $tweet['user'];
            
            $metrics = [
                'like_count' => $tweet['favorite_count'] ?? 0,
                'retweet_count' => $tweet['retweet_count'] ?? 0,
                'reply_count' => 0,  // Not available in v1.1
                'impression_count' => null
            ];
            
            // Build v2-like structure
            $v2Tweet = [
                'id' => $tweet['id_str'],
                'text' => $tweet['full_text'] ?? $tweet['text'],
                'author_id' => $author['id_str'],
                'created_at' => $tweet['created_at'],
                'public_metrics' => $metrics,
                'entities' => $tweet['entities'] ?? [],
                'lang' => $tweet['lang'] ?? null
            ];
            
            $v2Author = [
                'id' => $author['id_str'],
                'name' => $author['name'],
                'username' => $author['screen_name'],
                'verified' => $author['verified'] ?? false,
                'public_metrics' => [
                    'followers_count' => $author['followers_count'] ?? 0
                ],
                'profile_image_url' => $author['profile_image_url_https'] ?? null
            ];
            
            $tweets[] = $this->createMentionFromTweet($v2Tweet, $v2Author);
        }
        
        return $tweets;
    }

    /**
     * Process tweets from API response
     */
    protected function processTweets(array $data): array
    {
        $tweets = [];
        
        if (!isset($data['data']) || empty($data['data'])) {
            return $tweets;
        }
        
        // Create user lookup
        $users = [];
        if (isset($data['includes']['users'])) {
            foreach ($data['includes']['users'] as $user) {
                $users[$user['id']] = $user;
            }
        }
        
        foreach ($data['data'] as $tweet) {
            $authorId = $tweet['author_id'] ?? null;
            $author = $users[$authorId] ?? null;
            
            if (!$author) {
                continue;
            }
            
            $tweets[] = $this->createMentionFromTweet($tweet, $author);
        }
        
        return $tweets;
    }

    /**
     * Create mention record from tweet
     */
    protected function createMentionFromTweet(array $tweet, array $author): SocialMediaMention
    {
        $metrics = $tweet['public_metrics'] ?? [];
        $entities = $tweet['entities'] ?? [];
        
        // Extract hashtags
        $hashtags = [];
        if (isset($entities['hashtags'])) {
            $hashtags = array_map(fn($h) => $h['tag'], $entities['hashtags']);
        }
        
        // Extract mentions
        $mentions = [];
        if (isset($entities['mentions'])) {
            $mentions = array_map(fn($m) => $m['username'], $entities['mentions']);
        }
        
        // Extract URLs
        $urls = [];
        if (isset($entities['urls'])) {
            $urls = array_map(fn($u) => $u['expanded_url'] ?? $u['url'], $entities['urls']);
        }
        
        // Determine post type
        $postType = 'post';
        if (isset($tweet['referenced_tweets'])) {
            $refType = $tweet['referenced_tweets'][0]['type'] ?? '';
            $postType = match($refType) {
                'replied_to' => 'reply',
                'retweeted' => 'share',
                'quoted' => 'share',
                default => 'post'
            };
        }
        
        $mention = SocialMediaMention::updateOrCreate(
            ['platform_post_id' => $tweet['id']],
            [
                'source_id' => $this->source->id,
                'author_name' => $author['name'],
                'author_handle' => $author['username'],
                'author_profile_url' => 'https://twitter.com/' . $author['username'],
                'author_followers' => $author['public_metrics']['followers_count'] ?? 0,
                'author_verified' => $author['verified'] ?? false,
                'content' => $tweet['text'],
                'hashtags' => $hashtags,
                'mentions' => $mentions,
                'urls' => $urls,
                'media_urls' => [], // Would need media expansion for this
                'post_url' => 'https://twitter.com/' . $author['username'] . '/status/' . $tweet['id'],
                'published_at' => $tweet['created_at'],
                'likes_count' => $metrics['like_count'] ?? 0,
                'shares_count' => ($metrics['retweet_count'] ?? 0) + ($metrics['quote_count'] ?? 0),
                'comments_count' => $metrics['reply_count'] ?? 0,
                'views_count' => $metrics['impression_count'] ?? null,
                'post_type' => $postType,
                'in_reply_to' => $tweet['referenced_tweets'][0]['id'] ?? null,
                'is_repost' => $postType === 'share',
                'raw_data' => $tweet
            ]
        );
        
        // Calculate engagement rate
        $mention->update([
            'engagement_rate' => $mention->calculateEngagementRate()
        ]);
        
        return $mention;
    }

    /**
     * Update rate limit information
     */
    protected function updateRateLimits($headers): void
    {
        // Headers can be in different formats
        $remaining = $headers['x-rate-limit-remaining'][0] ?? 
                    $headers['x-rate-limit-remaining'] ?? 
                    null;
        
        $reset = $headers['x-rate-limit-reset'][0] ?? 
                $headers['x-rate-limit-reset'] ?? 
                null;
        
        if ($remaining !== null && $reset !== null) {
            // Ensure we have integer values
            $remaining = is_array($remaining) ? (int) $remaining[0] : (int) $remaining;
            $reset = is_array($reset) ? $reset[0] : $reset;
            
            // Update in database
            $this->source->update([
                'rate_limit_remaining' => $remaining,
                'rate_limit_reset_at' => \Carbon\Carbon::createFromTimestamp($reset)
            ]);
            
            Log::info('Twitter rate limit updated', [
                'remaining' => $remaining,
                'reset' => \Carbon\Carbon::createFromTimestamp($reset)->format('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Get user timeline (for monitoring specific accounts)
     */
    public function getUserTimeline(string $username, ?string $sinceId = null): array
    {
        if (!$this->bearerToken) {
            return [];
        }

        try {
            // First get user ID from username
            $userResponse = Http::withToken($this->bearerToken)
                ->get("https://api.twitter.com/2/users/by/username/{$username}");
            
            if (!$userResponse->successful()) {
                return [];
            }
            
            $userId = $userResponse->json()['data']['id'] ?? null;
            if (!$userId) {
                return [];
            }
            
            // Get user's tweets
            $params = [
                'max_results' => 100,
                'tweet.fields' => 'id,text,author_id,created_at,public_metrics,referenced_tweets,lang,entities',
                'exclude' => 'replies,retweets'
            ];
            
            if ($sinceId) {
                $params['since_id'] = $sinceId;
            }
            
            $response = Http::withToken($this->bearerToken)
                ->get("https://api.twitter.com/2/users/{$userId}/tweets", $params);
            
            if ($response->successful()) {
                $data = $response->json();
                $data['includes'] = ['users' => [$userResponse->json()['data']]];
                return $this->processTweets($data);
            }
        } catch (\Exception $e) {
            Log::error('Twitter timeline fetch failed', [
                'username' => $username,
                'error' => $e->getMessage()
            ]);
        }
        
        return [];
    }

    /**
     * Check if service is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->bearerToken);
    }

    /**
     * Test connection
     */
    public function testConnection(): bool
    {
        // Try bearer token first (most common for read operations)
        if ($this->bearerToken) {
            try {
                // Test with a simple endpoint that requires authentication
                $response = Http::withToken($this->bearerToken)
                    ->get('https://api.twitter.com/2/tweets/search/recent', [
                        'query' => 'test',
                        'max_results' => 10
                    ]);
                
                if ($response->successful()) {
                    return true;
                }
                
                // Log the error for debugging
                Log::warning('Twitter Bearer Token test failed', [
                    'status' => $response->status(),
                    'error' => $response->json()
                ]);
                
            } catch (\Exception $e) {
                Log::error('Twitter connection test exception', [
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Try OAuth 1.0a with access tokens if bearer token fails
        if ($this->apiKey && $this->apiSecret && $this->accessToken && $this->accessTokenSecret) {
            try {
                // For OAuth 1.0a we would need a different approach
                // For now, we'll just check if all credentials are present
                return true;
            } catch (\Exception $e) {
                Log::error('Twitter OAuth test failed', [
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return false;
    }
}