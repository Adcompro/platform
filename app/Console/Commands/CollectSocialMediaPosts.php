<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SocialMediaSource;
use App\Models\SocialMediaMention;
use App\Models\UserMediaMonitor;
use App\Services\Social\TwitterService;
use App\Services\Social\LinkedInService;
use App\Jobs\AnalyzeMediaMention;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CollectSocialMediaPosts extends Command
{
    protected $signature = 'social:collect {--source=} {--debug}';
    protected $description = 'Collect posts from social media APIs';

    public function handle()
    {
        $sourceId = $this->option('source');
        $debug = $this->option('debug');
        
        // Get active social media sources
        $query = SocialMediaSource::where('is_active', 1);
        
        if ($sourceId) {
            $query->where('id', $sourceId);
        }
        
        $sources = $query->get();
        
        if ($sources->isEmpty()) {
            $this->info('No active social media sources found.');
            return 0;
        }
        
        foreach ($sources as $source) {
            try {
                $this->info("Processing {$source->platform} source: {$source->name}");
                
                // Check rate limiting
                if ($source->isRateLimited()) {
                    $this->warn("Source is rate limited. Skipping until {$source->rate_limit_reset}");
                    continue;
                }
                
                // Process based on platform
                $mentions = match($source->platform) {
                    'twitter' => $this->collectTwitterPosts($source),
                    'linkedin' => $this->collectLinkedInPosts($source),
                    'facebook' => $this->collectFacebookPosts($source),
                    'instagram' => $this->collectInstagramPosts($source),
                    default => []
                };
                
                if ($debug) {
                    $this->info("Collected " . count($mentions) . " posts");
                }
                
                // Process each mention
                foreach ($mentions as $mention) {
                    $this->processSocialMention($mention, $source, $debug);
                }
                
                // Update last collected timestamp
                $source->update(['last_collected_at' => now()]);
                
            } catch (\Exception $e) {
                Log::error("Social media collection failed for source {$source->id}", [
                    'platform' => $source->platform,
                    'error' => $e->getMessage()
                ]);
                $this->error("Failed to collect from {$source->name}: " . $e->getMessage());
            }
        }
        
        $this->info('Social media collection completed.');
        return 0;
    }
    
    /**
     * Collect posts from Twitter
     */
    protected function collectTwitterPosts(SocialMediaSource $source): array
    {
        $service = new TwitterService($source);
        
        if (!$service->isConfigured()) {
            $this->warn("Twitter source not properly configured");
            return [];
        }
        
        $mentions = [];
        
        // Get keywords from all active monitors
        $monitors = UserMediaMonitor::where('is_active', true)
            ->whereNotNull('keywords')
            ->get();
        
        $allKeywords = [];
        foreach ($monitors as $monitor) {
            $keywords = is_string($monitor->keywords) 
                ? json_decode($monitor->keywords, true) 
                : $monitor->keywords;
            if (is_array($keywords)) {
                $allKeywords = array_merge($allKeywords, $keywords);
            }
        }
        
        $allKeywords = array_unique($allKeywords);
        
        if (empty($allKeywords)) {
            $this->info("No keywords to search for");
            return [];
        }
        
        // Get last tweet ID for pagination
        $lastMention = SocialMediaMention::where('source_id', $source->id)
            ->orderBy('platform_post_id', 'desc')
            ->first();
        
        $sinceId = $lastMention ? $lastMention->platform_post_id : null;
        
        // Search tweets
        $mentions = $service->searchTweets($allKeywords, $sinceId);
        
        // Also check specific accounts if configured
        if ($source->account_name) {
            $timelineMentions = $service->getUserTimeline($source->account_name, $sinceId);
            $mentions = array_merge($mentions, $timelineMentions);
        }
        
        return $mentions;
    }
    
    /**
     * Collect posts from LinkedIn
     */
    protected function collectLinkedInPosts(SocialMediaSource $source): array
    {
        $service = new LinkedInService($source);
        
        if (!$service->isConfigured()) {
            $this->warn("LinkedIn source not properly configured");
            return [];
        }
        
        $mentions = [];
        
        // LinkedIn primarily for monitoring own company posts
        if ($source->account_id) {
            $mentions = $service->getOrganizationShares($source->account_id);
        }
        
        // Get keywords for filtering
        $monitors = UserMediaMonitor::where('is_active', true)
            ->whereNotNull('keywords')
            ->get();
        
        $allKeywords = [];
        foreach ($monitors as $monitor) {
            $keywords = is_string($monitor->keywords) 
                ? json_decode($monitor->keywords, true) 
                : $monitor->keywords;
            if (is_array($keywords)) {
                $allKeywords = array_merge($allKeywords, $keywords);
            }
        }
        
        if (!empty($allKeywords)) {
            $filteredMentions = $service->searchPosts($allKeywords);
            $mentions = array_merge($mentions, $filteredMentions);
        }
        
        return $mentions;
    }
    
    /**
     * Collect posts from Facebook (placeholder)
     */
    protected function collectFacebookPosts(SocialMediaSource $source): array
    {
        // Facebook Graph API implementation would go here
        // Requires Facebook app and page access tokens
        $this->warn("Facebook integration not yet implemented");
        return [];
    }
    
    /**
     * Collect posts from Instagram (placeholder)
     */
    protected function collectInstagramPosts(SocialMediaSource $source): array
    {
        // Instagram Basic Display API implementation would go here
        // Requires Instagram app and user access tokens
        $this->warn("Instagram integration not yet implemented");
        return [];
    }
    
    /**
     * Process a social media mention
     */
    protected function processSocialMention(SocialMediaMention $mention, SocialMediaSource $source, bool $debug = false): void
    {
        try {
            // Check for user monitors that match this mention
            $monitors = UserMediaMonitor::where('is_active', true)->get();
            
            foreach ($monitors as $monitor) {
                $keywords = is_string($monitor->keywords) 
                    ? json_decode($monitor->keywords, true) 
                    : $monitor->keywords;
                
                if (empty($keywords)) {
                    continue;
                }
                
                // Check if mention matches any keywords
                $matchFound = false;
                $matchedKeywords = [];
                
                $contentToSearch = strtolower($mention->content);
                
                foreach ($keywords as $keyword) {
                    if (stripos($contentToSearch, $keyword) !== false) {
                        $matchFound = true;
                        $matchedKeywords[] = $keyword;
                    }
                }
                
                if ($matchFound) {
                    if ($debug) {
                        $this->info("Match found for keywords: " . implode(', ', $matchedKeywords));
                    }
                    
                    // Create user social mention
                    $userMention = $mention->userMentions()->updateOrCreate(
                        [
                            'user_id' => $monitor->user_id,
                            'monitor_id' => $monitor->id,
                            'social_mention_id' => $mention->id
                        ],
                        [
                            'matched_keywords' => $matchedKeywords,
                            'relevance_score' => $this->calculateRelevanceScore($mention, $matchedKeywords),
                            'sentiment' => null, // Will be set by AI analysis
                            'is_read' => false,
                            'requires_response' => $this->shouldRequireResponse($mention)
                        ]
                    );
                    
                    // Queue for AI analysis if configured
                    if (config('services.claude.api_key')) {
                        AnalyzeMediaMention::dispatch($userMention, 'social');
                    }
                    
                    // Check for campaign assignment
                    if ($monitor->campaign_id) {
                        $this->assignToCampaign($mention, $monitor->campaign_id, $matchedKeywords);
                    }
                }
            }
            
            // Store engagement metrics
            $mention->engagementMetrics()->create([
                'likes_count' => $mention->likes_count,
                'shares_count' => $mention->shares_count,
                'comments_count' => $mention->comments_count,
                'views_count' => $mention->views_count,
                'engagement_rate' => $mention->engagement_rate,
                'measured_at' => now()
            ]);
            
        } catch (\Exception $e) {
            Log::error("Failed to process social mention", [
                'mention_id' => $mention->id,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Calculate relevance score based on various factors
     */
    protected function calculateRelevanceScore(SocialMediaMention $mention, array $matchedKeywords): int
    {
        $score = 50; // Base score
        
        // More keywords matched = higher score
        $score += count($matchedKeywords) * 10;
        
        // Verified account = higher score
        if ($mention->author_verified) {
            $score += 20;
        }
        
        // High engagement = higher score
        if ($mention->engagement_rate > 5) {
            $score += 15;
        } elseif ($mention->engagement_rate > 2) {
            $score += 10;
        }
        
        // Influencer = higher score
        if ($mention->isInfluencer()) {
            $score += 15;
        }
        
        // Cap at 100
        return min($score, 100);
    }
    
    /**
     * Determine if mention requires response
     */
    protected function shouldRequireResponse(SocialMediaMention $mention): bool
    {
        // Questions or mentions typically need responses
        if (str_contains($mention->content, '?')) {
            return true;
        }
        
        // High engagement posts might need response
        if ($mention->engagement_rate > 10) {
            return true;
        }
        
        // Influencer posts might need response
        if ($mention->isInfluencer()) {
            return true;
        }
        
        // Reply type posts often need response
        if ($mention->post_type === 'reply') {
            return true;
        }
        
        return false;
    }
    
    /**
     * Assign mention to campaign
     */
    protected function assignToCampaign(SocialMediaMention $mention, int $campaignId, array $matchedKeywords): void
    {
        $campaign = \App\Models\ProjectMediaCampaign::find($campaignId);
        if (!$campaign) {
            return;
        }
        
        // Calculate confidence score
        $campaignKeywords = is_string($campaign->keywords) 
            ? json_decode($campaign->keywords, true) 
            : $campaign->keywords;
        
        $matchCount = 0;
        if (is_array($campaignKeywords)) {
            foreach ($matchedKeywords as $keyword) {
                if (in_array($keyword, $campaignKeywords)) {
                    $matchCount++;
                }
            }
        }
        
        $confidence = $matchCount > 0 ? min(($matchCount / count($campaignKeywords)) * 100, 100) : 50;
        
        // Create project social mention
        \App\Models\ProjectSocialMention::updateOrCreate(
            [
                'social_mention_id' => $mention->id,
                'campaign_id' => $campaignId
            ],
            [
                'project_id' => $campaign->project_id,
                'assignment_method' => 'automatic',
                'confidence_score' => $confidence,
                'notes' => 'Auto-assigned based on keyword match: ' . implode(', ', $matchedKeywords)
            ]
        );
    }
}