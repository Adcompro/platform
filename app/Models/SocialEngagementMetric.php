<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialEngagementMetric extends Model
{
    protected $fillable = [
        'social_mention_id',
        'likes_count',
        'shares_count',
        'comments_count',
        'views_count',
        'engagement_rate',
        'measured_at'
    ];

    protected $casts = [
        'likes_count' => 'integer',
        'shares_count' => 'integer',
        'comments_count' => 'integer',
        'views_count' => 'integer',
        'engagement_rate' => 'decimal:2',
        'measured_at' => 'datetime'
    ];

    /**
     * The social mention this metric belongs to
     */
    public function socialMention(): BelongsTo
    {
        return $this->belongsTo(SocialMediaMention::class, 'social_mention_id');
    }

    /**
     * Get growth rate compared to previous measurement
     */
    public function getGrowthRate(): array
    {
        $previous = self::where('social_mention_id', $this->social_mention_id)
            ->where('id', '<', $this->id)
            ->orderBy('id', 'desc')
            ->first();

        if (!$previous) {
            return [
                'likes' => 0,
                'shares' => 0,
                'comments' => 0,
                'views' => 0,
                'engagement' => 0
            ];
        }

        return [
            'likes' => $this->calculateGrowth($previous->likes_count, $this->likes_count),
            'shares' => $this->calculateGrowth($previous->shares_count, $this->shares_count),
            'comments' => $this->calculateGrowth($previous->comments_count, $this->comments_count),
            'views' => $this->calculateGrowth($previous->views_count, $this->views_count),
            'engagement' => $this->calculateGrowth($previous->engagement_rate, $this->engagement_rate)
        ];
    }

    /**
     * Calculate growth percentage
     */
    private function calculateGrowth($old, $new): float
    {
        if ($old == 0) {
            return $new > 0 ? 100 : 0;
        }
        
        return round((($new - $old) / $old) * 100, 2);
    }

    /**
     * Get total engagement count
     */
    public function getTotalEngagement(): int
    {
        return $this->likes_count + $this->shares_count + $this->comments_count;
    }

    /**
     * Check if metrics are growing
     */
    public function isGrowing(): bool
    {
        $growth = $this->getGrowthRate();
        return $growth['engagement'] > 0;
    }

    /**
     * Check if viral (significant growth)
     */
    public function isViral(): bool
    {
        $growth = $this->getGrowthRate();
        
        // Consider viral if engagement grew by 50% or more
        return $growth['engagement'] >= 50;
    }

    /**
     * Format metric for display
     */
    public function formatMetric($value): string
    {
        if ($value >= 1000000) {
            return round($value / 1000000, 1) . 'M';
        } elseif ($value >= 1000) {
            return round($value / 1000, 1) . 'K';
        }
        return (string) $value;
    }

    /**
     * Get formatted metrics
     */
    public function getFormattedMetrics(): array
    {
        return [
            'likes' => $this->formatMetric($this->likes_count),
            'shares' => $this->formatMetric($this->shares_count),
            'comments' => $this->formatMetric($this->comments_count),
            'views' => $this->views_count ? $this->formatMetric($this->views_count) : null,
            'total' => $this->formatMetric($this->getTotalEngagement())
        ];
    }
}