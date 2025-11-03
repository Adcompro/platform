<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMediaMention extends Model
{
    protected $fillable = [
        'project_id',
        'campaign_id',
        'user_media_mention_id',
        'assigned_by',
        'assignment_method',
        'confidence_score',
        'notes'
    ];

    protected $casts = [
        'confidence_score' => 'integer'
    ];

    /**
     * The project this mention belongs to
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * The campaign this mention is linked to
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(ProjectMediaCampaign::class, 'campaign_id');
    }

    /**
     * The original user media mention
     */
    public function userMention(): BelongsTo
    {
        return $this->belongsTo(UserMediaMention::class, 'user_media_mention_id');
    }

    /**
     * User who assigned this mention
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Get assignment method label
     */
    public function getAssignmentMethodLabel(): string
    {
        return match($this->assignment_method) {
            'automatic' => 'Auto-assigned',
            'manual' => 'Manually assigned',
            'ai_suggested' => 'AI Suggested',
            default => 'Unknown'
        };
    }

    /**
     * Get confidence badge color
     */
    public function getConfidenceBadgeColor(): string
    {
        if ($this->confidence_score >= 80) {
            return 'green';
        } elseif ($this->confidence_score >= 60) {
            return 'yellow';
        } else {
            return 'red';
        }
    }

    /**
     * Scope for high confidence assignments
     */
    public function scopeHighConfidence($query, $threshold = 70)
    {
        return $query->where('confidence_score', '>=', $threshold);
    }

    /**
     * Scope for automatic assignments
     */
    public function scopeAutomatic($query)
    {
        return $query->where('assignment_method', 'automatic');
    }

    /**
     * Scope for manual assignments
     */
    public function scopeManual($query)
    {
        return $query->where('assignment_method', 'manual');
    }
}