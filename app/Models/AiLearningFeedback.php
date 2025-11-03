<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiLearningFeedback extends Model
{
    use HasFactory;

    protected $table = 'ai_learning_feedback';

    protected $fillable = [
        'project_id',
        'time_entry_id',
        'original_description',
        'ai_suggestion',
        'correct_subtask',
        'feedback_type',
        'learning_notes',
        'confidence_before',
        'confidence_after',
        'reviewed_by',
        'applied_to_ai'
    ];

    protected $casts = [
        'confidence_before' => 'float',
        'confidence_after' => 'float',
        'applied_to_ai' => 'boolean'
    ];

    /**
     * Get the project this feedback belongs to
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the time entry this feedback is for
     */
    public function timeEntry(): BelongsTo
    {
        return $this->belongsTo(TimeEntry::class);
    }

    /**
     * Get the user who reviewed this
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Mark feedback as applied to AI
     */
    public function markAsApplied(): void
    {
        $this->update(['applied_to_ai' => true]);
    }

    /**
     * Get learning patterns for a project
     */
    public static function getLearningPatterns($projectId, $limit = 50)
    {
        return self::where('project_id', $projectId)
            ->where('feedback_type', '!=', 'negative')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($feedback) {
                return [
                    'original_description' => $feedback->original_description,
                    'improved_description' => $feedback->correct_subtask, // This now holds the correct description
                    'confidence' => $feedback->confidence_after ?? $feedback->confidence_before
                ];
            });
    }

    /**
     * Get negative patterns to avoid
     */
    public static function getNegativePatterns($projectId, $limit = 20)
    {
        return self::where('project_id', $projectId)
            ->where('feedback_type', 'negative')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->pluck('ai_suggestion')
            ->unique()
            ->values();
    }
}