<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RssFeedCache extends Model
{
    protected $table = 'rss_feed_cache';
    
    protected $fillable = [
        'source_id',
        'guid',
        'title',
        'link',
        'description',
        'pub_date',
        'author',
        'raw_content',
        'processed',
        'processed_at'
    ];

    protected $casts = [
        'pub_date' => 'datetime',
        'processed' => 'boolean',
        'processed_at' => 'datetime'
    ];

    /**
     * The source this item belongs to
     */
    public function source(): BelongsTo
    {
        return $this->belongsTo(MediaSource::class, 'source_id');
    }

    /**
     * Mark as processed
     */
    public function markAsProcessed(): void
    {
        $this->update([
            'processed' => true,
            'processed_at' => now()
        ]);
    }

    /**
     * Get full content for analysis
     */
    public function getContentForAnalysis(): string
    {
        $content = $this->title . ' ';
        
        if ($this->description) {
            $content .= strip_tags($this->description) . ' ';
        }
        
        if ($this->raw_content) {
            $content .= strip_tags($this->raw_content);
        }
        
        return $content;
    }

    /**
     * Scope for unprocessed items
     */
    public function scopeUnprocessed($query)
    {
        return $query->where('processed', false);
    }

    /**
     * Scope for recent items
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('pub_date', '>=', now()->subDays($days));
    }
}