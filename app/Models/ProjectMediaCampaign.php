<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\User;

class ProjectMediaCampaign extends Model
{
    protected $fillable = [
        'project_id',
        'parent_id',
        'name',
        'description',
        'press_release_date',
        'campaign_type',
        'target_audience',
        'expected_reach',
        'actual_reach',
        'keywords',
        'status',
        'budget',
        'actual_cost',
        'notes'
    ];

    protected $casts = [
        'press_release_date' => 'date',
        'keywords' => 'array',
        'expected_reach' => 'integer',
        'actual_reach' => 'integer',
        'budget' => 'decimal:2',
        'actual_cost' => 'decimal:2'
    ];

    /**
     * The project this campaign belongs to
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Parent campaign (for sub-campaigns)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProjectMediaCampaign::class, 'parent_id');
    }

    /**
     * Child campaigns
     */
    public function children(): HasMany
    {
        return $this->hasMany(ProjectMediaCampaign::class, 'parent_id');
    }

    /**
     * The associated media monitor
     */
    public function monitor(): HasOne
    {
        return $this->hasOne(UserMediaMonitor::class, 'campaign_id');
    }

    /**
     * Media mentions linked to this campaign
     */
    public function mentions(): HasMany
    {
        return $this->hasMany(ProjectMediaMention::class, 'campaign_id');
    }

    /**
     * Create or update the associated monitor
     */
    public function syncMonitor(): void
    {
        // Get first project user or admin
        $user = $this->project->users()->first() ?: User::where('role', 'admin')->first();

        if (!$user) {
            // If still no user, get any super_admin
            $user = User::where('role', 'super_admin')->first();
        }

        if (!$user) {
            return;
        }

        // Find existing monitor or create new one
        $monitor = $this->monitor()->first();
        
        if (!$monitor) {
            $monitor = new UserMediaMonitor();
            $monitor->user_id = $user->id;
            $monitor->campaign_id = $this->id;
        }
        
        // Update monitor fields
        $monitor->name = "Campaign: {$this->name}";
        $monitor->keywords = $this->keywords ?: [];
        $monitor->is_active = $this->status === 'active';
        $monitor->email_alerts = true;
        $monitor->alert_frequency = 'daily';
        
        $monitor->save();
    }

    /**
     * Calculate ROI
     */
    public function calculateROI(): ?float
    {
        if ($this->actual_cost <= 0) {
            return null;
        }

        // Calculate value based on reach and mentions (using confidence_score from project_media_mentions)
        $mentionValue = $this->mentions()->sum('confidence_score') * 10; // €10 per confidence point
        $reachValue = ($this->actual_reach ?? 0) * 0.05; // €0.05 per reach
        $totalValue = $mentionValue + $reachValue;

        return (($totalValue - $this->actual_cost) / $this->actual_cost) * 100;
    }

    /**
     * Get performance metrics
     */
    public function getMetrics(): array
    {
        // Get project mentions with their related user mentions
        $projectMentions = $this->mentions()->with('userMention')->get();
        
        // Extract user mentions for calculations
        $userMentions = $projectMentions->pluck('userMention')->filter();
        
        return [
            'total_mentions' => $projectMentions->count(),
            'high_relevance_mentions' => $projectMentions->where('confidence_score', '>=', 70)->count(),
            'positive_sentiment' => $userMentions->where('sentiment', 'positive')->count(),
            'negative_sentiment' => $userMentions->where('sentiment', 'negative')->count(),
            'neutral_sentiment' => $userMentions->where('sentiment', 'neutral')->count(),
            'average_relevance' => $projectMentions->avg('confidence_score') ?? 0,
            'reach_achievement' => $this->expected_reach > 0 
                ? round(($this->actual_reach ?? 0) / $this->expected_reach * 100, 2) 
                : 0,
            'roi' => $this->calculateROI()
        ];
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeColor(): string
    {
        return match($this->status) {
            'planning' => 'blue',
            'active' => 'green',
            'completed' => 'gray',
            'on_hold' => 'yellow',
            default => 'gray'
        };
    }

    /**
     * Scope for active campaigns
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for main campaigns (not sub-campaigns)
     */
    public function scopeMain($query)
    {
        return $query->whereNull('parent_id');
    }
}