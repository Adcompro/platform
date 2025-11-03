<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSocialMention extends Model
{
    protected $fillable = [
        'user_id',
        'monitor_id',
        'social_mention_id',
        'relevance_score',
        'is_read',
        'is_starred',
        'requires_response',
        'response_draft',
        'responded_at',
        'sentiment',
        'matched_keywords',
        'ai_summary'
    ];

    protected $casts = [
        'relevance_score' => 'integer',
        'is_read' => 'boolean',
        'is_starred' => 'boolean',
        'requires_response' => 'boolean',
        'responded_at' => 'datetime',
        'matched_keywords' => 'array'
    ];

    /**
     * The user who owns this mention
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The monitor that found this mention
     */
    public function monitor(): BelongsTo
    {
        return $this->belongsTo(UserMediaMonitor::class, 'monitor_id');
    }

    /**
     * The social media post
     */
    public function socialMention(): BelongsTo
    {
        return $this->belongsTo(SocialMediaMention::class, 'social_mention_id');
    }

    /**
     * Mark as read
     */
    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }

    /**
     * Toggle star status
     */
    public function toggleStar(): void
    {
        $this->update(['is_starred' => !$this->is_starred]);
    }

    /**
     * Mark as requiring response
     */
    public function markForResponse(): void
    {
        $this->update(['requires_response' => true]);
    }

    /**
     * Mark as responded
     */
    public function markAsResponded(): void
    {
        $this->update([
            'requires_response' => false,
            'responded_at' => now()
        ]);
    }

    /**
     * Get sentiment emoji
     */
    public function getSentimentEmoji(): string
    {
        return match($this->sentiment) {
            'positive' => '😊',
            'negative' => '😟',
            'neutral' => '😐',
            default => ''
        };
    }

    /**
     * Get relevance badge color
     */
    public function getRelevanceBadgeColor(): string
    {
        if ($this->relevance_score >= 80) {
            return 'red';
        } elseif ($this->relevance_score >= 60) {
            return 'yellow';
        } else {
            return 'gray';
        }
    }

    /**
     * Check if needs immediate attention
     */
    public function needsAttention(): bool
    {
        // High relevance + negative sentiment = needs attention
        if ($this->relevance_score >= 70 && $this->sentiment === 'negative') {
            return true;
        }

        // From influencer + requires response
        if ($this->socialMention->isInfluencer() && $this->requires_response) {
            return true;
        }

        // High engagement + not responded
        if ($this->socialMention->engagement_rate >= 5 && !$this->responded_at) {
            return true;
        }

        return false;
    }

    /**
     * Scope for unread mentions
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope for starred mentions
     */
    public function scopeStarred($query)
    {
        return $query->where('is_starred', true);
    }

    /**
     * Scope for mentions needing response
     */
    public function scopeNeedsResponse($query)
    {
        return $query->where('requires_response', true)
                     ->whereNull('responded_at');
    }

    /**
     * Scope for high relevance
     */
    public function scopeHighRelevance($query, $threshold = 70)
    {
        return $query->where('relevance_score', '>=', $threshold);
    }
}