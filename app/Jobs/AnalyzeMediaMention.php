<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\RssFeedCache;
use App\Models\UserMediaMonitor;
use App\Models\UserMediaMention;
use App\Models\ProjectMediaCampaign;
use App\Models\ProjectMediaMention;
use App\Services\ClaudeAIService;
use Illuminate\Support\Facades\Log;

class AnalyzeMediaMention implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The RSS feed item to analyze
     */
    public $feedItem;
    
    /**
     * Number of times to retry the job
     */
    public $tries = 3;
    
    /**
     * Number of seconds before the job times out
     */
    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(RssFeedCache $feedItem)
    {
        $this->feedItem = $feedItem;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            // Get all active monitors
            $monitors = UserMediaMonitor::where('is_active', true)->get();
            
            if ($monitors->isEmpty()) {
                // Mark as processed even if no monitors
                $this->feedItem->markAsProcessed();
                return;
            }
            
            // Get content for analysis
            $content = $this->feedItem->getContentForAnalysis();
            
            // Check each monitor for matches
            foreach ($monitors as $monitor) {
                $matchResult = $monitor->matchesArticle($content);
                
                if ($matchResult['matches']) {
                    // Create mention for this user
                    $this->createMention($monitor, $matchResult);
                }
            }
            
            // Mark feed item as processed
            $this->feedItem->markAsProcessed();
            
        } catch (\Exception $e) {
            Log::error('Media mention analysis failed', [
                'feed_item_id' => $this->feedItem->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Create a mention for the user
     */
    private function createMention(UserMediaMonitor $monitor, array $matchResult)
    {
        try {
            // Check if mention already exists for this user (using article URL as unique identifier)
            $existingMention = UserMediaMention::where('user_id', $monitor->user_id)
                ->where('monitor_id', $monitor->id)
                ->where('article_url', $this->feedItem->link)
                ->first();
            
            if ($existingMention) {
                // Update relevance score if higher
                if ($matchResult['score'] > $existingMention->relevance_score) {
                    $existingMention->update([
                        'relevance_score' => $matchResult['score'],
                        'matched_keywords' => $matchResult['matched_keywords']
                    ]);
                }
                return;
            }
            
            // Perform AI sentiment analysis if enabled and score is high enough
            $sentiment = 'neutral';
            $aiSummary = null;
            
            if ($matchResult['score'] >= 60) {
                $aiAnalysis = $this->performAiAnalysis();
                if ($aiAnalysis) {
                    $sentiment = $aiAnalysis['sentiment'];
                    $aiSummary = $aiAnalysis['summary'];
                }
            }
            
            // Create new mention
            $mention = UserMediaMention::create([
                'user_id' => $monitor->user_id,
                'monitor_id' => $monitor->id,
                'source_name' => $this->feedItem->source->name,
                'article_title' => $this->feedItem->title,
                'article_url' => $this->feedItem->link,
                'article_excerpt' => $this->getExcerpt($this->feedItem->description),
                'published_at' => $this->feedItem->pub_date ?? now(),
                'found_keywords' => $matchResult['keywords'] ?? [],
                'relevance_score' => $matchResult['score'],
                'sentiment' => $sentiment,
                'ai_summary' => $aiSummary,
                'is_read' => false,
                'is_starred' => false
            ]);
            
            // Send notification if configured
            if ($monitor->email_alerts) {
                $this->sendNotification($monitor, $mention);
            }
            
            // Check if monitor is linked to a campaign
            if ($monitor->campaign_id) {
                $this->assignMentionToCampaign($mention, $monitor->campaign_id, $matchResult['score']);
            } else {
                // Try to auto-assign to campaigns based on keywords
                $this->attemptAutoAssignment($mention);
            }
            
            Log::info('Media mention created', [
                'mention_id' => $mention->id,
                'user_id' => $monitor->user_id,
                'monitor_id' => $monitor->id,
                'relevance_score' => $matchResult['score']
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to create mention', [
                'monitor_id' => $monitor->id,
                'feed_item_id' => $this->feedItem->id,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Perform AI analysis on the content
     */
    private function performAiAnalysis(): ?array
    {
        try {
            $aiService = app(ClaudeAIService::class);
            
            $prompt = "Analyze this media article for a communication bureau. Provide:
1. Sentiment: positive, negative, or neutral
2. A brief 1-2 sentence summary focusing on key points relevant for PR monitoring

Article Title: {$this->feedItem->title}
Article Content: {$this->feedItem->getContentForAnalysis()}

Response format:
SENTIMENT: [positive/negative/neutral]
SUMMARY: [1-2 sentence summary]";
            
            $response = $aiService->generateCompletion($prompt, 'media_analysis');
            
            if ($response && $response['content']) {
                // Parse AI response
                $lines = explode("\n", $response['content']);
                $sentiment = 'neutral';
                $summary = '';
                
                foreach ($lines as $line) {
                    if (stripos($line, 'SENTIMENT:') !== false) {
                        $sentiment = strtolower(trim(str_replace('SENTIMENT:', '', $line)));
                        if (!in_array($sentiment, ['positive', 'negative', 'neutral'])) {
                            $sentiment = 'neutral';
                        }
                    } elseif (stripos($line, 'SUMMARY:') !== false) {
                        $summary = trim(str_replace('SUMMARY:', '', $line));
                    }
                }
                
                return [
                    'sentiment' => $sentiment,
                    'summary' => $summary ?: null
                ];
            }
            
        } catch (\Exception $e) {
            Log::warning('AI analysis failed', [
                'feed_item_id' => $this->feedItem->id,
                'error' => $e->getMessage()
            ]);
        }
        
        return null;
    }
    
    /**
     * Get excerpt from content
     */
    private function getExcerpt(?string $content, int $length = 200): ?string
    {
        if (!$content) {
            return null;
        }
        
        // Strip HTML tags
        $text = strip_tags($content);
        
        // Trim whitespace
        $text = trim($text);
        
        // Truncate if too long
        if (strlen($text) > $length) {
            $text = substr($text, 0, $length) . '...';
        }
        
        return $text;
    }
    
    /**
     * Send notification to user
     */
    private function sendNotification(UserMediaMonitor $monitor, UserMediaMention $mention)
    {
        // Check notification frequency
        if ($monitor->alert_frequency === 'realtime') {
            // Send immediate notification
            // TODO: Implement email notification
            Log::info('Should send realtime notification', [
                'user_id' => $monitor->user_id,
                'mention_id' => $mention->id
            ]);
        }
        // Hourly and daily digests are handled by a separate scheduled command
    }
    
    /**
     * Assign mention to a campaign
     */
    private function assignMentionToCampaign(UserMediaMention $mention, int $campaignId, int $confidenceScore)
    {
        try {
            $campaign = ProjectMediaCampaign::find($campaignId);
            if (!$campaign) {
                return;
            }
            
            // Check if already assigned
            $existing = ProjectMediaMention::where('user_media_mention_id', $mention->id)
                ->where('project_id', $campaign->project_id)
                ->first();
                
            if ($existing) {
                return;
            }
            
            // Create project mention link
            ProjectMediaMention::create([
                'project_id' => $campaign->project_id,
                'campaign_id' => $campaign->id,
                'user_media_mention_id' => $mention->id,
                'assigned_by' => null, // System assignment
                'assignment_method' => 'automatic',
                'confidence_score' => min($confidenceScore, 100)
            ]);
            
            Log::info('Mention assigned to campaign', [
                'mention_id' => $mention->id,
                'campaign_id' => $campaign->id,
                'project_id' => $campaign->project_id
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to assign mention to campaign', [
                'mention_id' => $mention->id,
                'campaign_id' => $campaignId,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Attempt to auto-assign mention to campaigns
     */
    private function attemptAutoAssignment(UserMediaMention $mention)
    {
        try {
            // Get active campaigns
            $campaigns = ProjectMediaCampaign::active()
                ->where('press_release_date', '<=', now())
                ->get();
            
            $content = $mention->article_title . ' ' . $mention->article_excerpt;
            $bestMatch = null;
            $bestScore = 0;
            
            foreach ($campaigns as $campaign) {
                $score = 0;
                
                // Check keywords match
                foreach ($campaign->keywords as $keyword) {
                    if (stripos($content, $keyword) !== false) {
                        $score += 20;
                    }
                }
                
                // Boost score if published near press release date
                $daysDiff = abs($mention->published_at->diffInDays($campaign->press_release_date));
                if ($daysDiff <= 7) {
                    $score += (7 - $daysDiff) * 5; // Max 35 points for same day
                }
                
                if ($score > $bestScore && $score >= 40) { // Minimum threshold
                    $bestScore = $score;
                    $bestMatch = $campaign;
                }
            }
            
            if ($bestMatch) {
                $this->assignMentionToCampaign($mention, $bestMatch->id, $bestScore);
            }
            
        } catch (\Exception $e) {
            Log::error('Auto-assignment failed', [
                'mention_id' => $mention->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}