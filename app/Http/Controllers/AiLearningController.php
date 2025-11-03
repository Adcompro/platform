<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\TimeEntry;
use App\Models\AiLearningFeedback;
use App\Models\ProjectAiSetting;
use App\Services\ClaudeAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AiLearningController extends Controller
{
    protected $aiService;
    
    public function __construct()
    {
        $this->aiService = new ClaudeAIService();
    }
    
    /**
     * Show AI Learning Review Dashboard
     */
    public function index(Request $request)
    {
        // Get projects for filter - voor super_admin alle projects, anders alleen waar user aan gekoppeld is
        if (Auth::user()->role === 'super_admin') {
            $projects = Project::orderBy('name')->get();
        } else {
            $projects = Project::where('company_id', Auth::user()->company_id)
                ->whereHas('users', function($q) {
                    $q->where('user_id', Auth::id());
                })
                ->orderBy('name')
                ->get();
        }
        
        // Get time entries with AI improvements
        $query = TimeEntry::with(['project', 'task', 'user'])
            ->whereNotNull('ai_improved_description')
            ->when(Auth::user()->role !== 'super_admin', function($q) {
                $q->where('company_id', Auth::user()->company_id);
            });
        
        // Apply filters
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
            Log::info('Filtering by project_id: ' . $request->project_id);
        }
        
        if ($request->filled('feedback_filter')) {
            switch($request->feedback_filter) {
                case 'unreviewed':
                    $query->whereNull('ai_feedback');
                    break;
                case 'good':
                    $query->where('ai_feedback', 'good');
                    break;
                case 'bad':
                    $query->where('ai_feedback', 'bad');
                    break;
                case 'adjusted':
                    $query->where('ai_feedback', 'adjusted');
                    break;
            }
        }
        
        if ($request->filled('date_from')) {
            $query->where('entry_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->where('entry_date', '<=', $request->date_to);
        }
        
        // Debug: Log query before execution
        Log::info('AI Learning Query: ' . $query->toSql());
        Log::info('Query bindings: ', $query->getBindings());
        
        $timeEntries = $query->orderBy('entry_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        Log::info('Found ' . $timeEntries->total() . ' AI time entries');
        
        // Get statistics
        $stats = $this->getAiStatistics($request->project_id);
        
        return view('ai-learning.index', compact('projects', 'timeEntries', 'stats'));
    }
    
    /**
     * Update AI feedback for a time entry
     */
    public function updateFeedback(Request $request, TimeEntry $timeEntry)
    {
        $request->validate([
            'feedback' => 'required|in:good,bad,adjusted',
            'corrected_subtask' => 'nullable|required_if:feedback,adjusted|string|max:500', // Now for description improvements
            'learning_notes' => 'nullable|string'
        ]);
        
        DB::beginTransaction();
        try {
            // Update time entry feedback
            $timeEntry->ai_feedback = $request->feedback;
            
            if ($request->feedback === 'adjusted' && $request->corrected_subtask) {
                // Update the actual description with the corrected version
                $timeEntry->description = $request->corrected_subtask;
            }
            
            $timeEntry->save();
            
            // Create learning feedback record
            AiLearningFeedback::create([
                'project_id' => $timeEntry->project_id,
                'time_entry_id' => $timeEntry->id,
                'original_description' => $timeEntry->original_description ?? $timeEntry->description,
                'ai_suggestion' => $timeEntry->ai_improved_description,
                'correct_subtask' => $request->corrected_subtask ?? $timeEntry->ai_improved_description,
                'feedback_type' => $request->feedback === 'good' ? 'positive' : 
                                  ($request->feedback === 'bad' ? 'negative' : 'correction'),
                'learning_notes' => $request->learning_notes,
                'confidence_before' => $timeEntry->ai_confidence,
                'confidence_after' => $request->feedback === 'good' ? 
                                     min(1.0, ($timeEntry->ai_confidence ?? 0.5) + 0.1) :
                                     max(0.0, ($timeEntry->ai_confidence ?? 0.5) - 0.1),
                'reviewed_by' => Auth::id(),
                'applied_to_ai' => false
            ]);
            
            // Update project AI settings with new description patterns if it's a correction
            if ($request->feedback === 'adjusted' && $request->corrected_subtask) {
                $this->updateProjectPatterns($timeEntry->project_id, 
                    $timeEntry->original_description ?? $timeEntry->description, 
                    $request->corrected_subtask);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Feedback saved successfully'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to save AI feedback', [
                'error' => $e->getMessage(),
                'time_entry_id' => $timeEntry->id
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to save feedback'
            ], 500);
        }
    }
    
    /**
     * Bulk review AI suggestions
     */
    public function bulkReview(Request $request)
    {
        $request->validate([
            'time_entry_ids' => 'required|array',
            'time_entry_ids.*' => 'exists:time_entries,id',
            'feedback' => 'required|in:good,bad'
        ]);
        
        DB::beginTransaction();
        try {
            $timeEntries = TimeEntry::whereIn('id', $request->time_entry_ids)->get();
            
            foreach ($timeEntries as $entry) {
                $entry->ai_feedback = $request->feedback;
                $entry->save();
                
                // Create learning feedback
                AiLearningFeedback::create([
                    'project_id' => $entry->project_id,
                    'time_entry_id' => $entry->id,
                    'original_description' => $entry->original_description ?? $entry->description,
                    'ai_suggestion' => $entry->ai_improved_description,
                    'correct_subtask' => $entry->ai_improved_description,
                    'feedback_type' => $request->feedback === 'good' ? 'positive' : 'negative',
                    'confidence_before' => $entry->ai_confidence,
                    'confidence_after' => $request->feedback === 'good' ? 
                                         min(1.0, ($entry->ai_confidence ?? 0.5) + 0.05) :
                                         max(0.0, ($entry->ai_confidence ?? 0.5) - 0.05),
                    'reviewed_by' => Auth::id(),
                    'applied_to_ai' => false
                ]);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => count($timeEntries) . ' entries reviewed successfully'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk review failed', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'error' => 'Bulk review failed'
            ], 500);
        }
    }
    
    /**
     * Apply learning to project AI settings
     */
    public function applyLearning($projectId)
    {
        try {
            $project = Project::findOrFail($projectId);
            
            // Check permissions
            if (Auth::user()->role !== 'super_admin' && 
                !$project->users()->where('user_id', Auth::id())->exists()) {
                abort(403);
            }
            
            // Get unapplied feedback
            $feedbacks = AiLearningFeedback::where('project_id', $projectId)
                ->where('applied_to_ai', false)
                ->where('feedback_type', '!=', 'negative')
                ->get();
            
            if ($feedbacks->isEmpty()) {
                return redirect()->back()->with('info', 'No new learning patterns to apply');
            }
            
            // Get or create AI settings
            $aiSettings = ProjectAiSetting::firstOrCreate(
                ['project_id' => $projectId],
                [
                    'use_global_settings' => false,
                    'is_active' => true
                ]
            );
            
            // Extract description improvement patterns
            $descriptionPatterns = [];
            $learningRules = [];
            
            foreach ($feedbacks as $feedback) {
                if ($feedback->feedback_type === 'correction' || $feedback->feedback_type === 'positive') {
                    // Store as example: "original -> improved"
                    $descriptionPatterns[] = substr($feedback->original_description, 0, 50) . ' → ' . $feedback->correct_subtask;
                    
                    // Extract learning rules from the patterns
                    if ($feedback->learning_notes) {
                        $learningRules[] = $feedback->learning_notes;
                    }
                }
            }
            
            // Update example patterns with description improvements
            $currentPatterns = $aiSettings->ai_example_patterns ?? [];
            $updatedPatterns = array_unique(array_merge($currentPatterns, $descriptionPatterns));
            
            // Keep only the 30 most recent unique patterns
            $aiSettings->ai_example_patterns = array_slice($updatedPatterns, -30);
            
            // Add learning rules to naming rules if any
            if (!empty($learningRules)) {
                $currentRules = $aiSettings->ai_naming_rules ?? '';
                $aiSettings->ai_naming_rules = $currentRules . "\n\n" . 
                    "LEARNED FROM FEEDBACK:\n" . implode("\n", array_unique($learningRules));
            }
            
            $aiSettings->save();
            
            // Mark feedbacks as applied
            $feedbacks->each(function($feedback) {
                $feedback->markAsApplied();
            });
            
            return redirect()->back()->with('success', 
                'Applied ' . count($feedbacks) . ' learning patterns to project AI settings');
            
        } catch (\Exception $e) {
            Log::error('Failed to apply learning', [
                'error' => $e->getMessage(),
                'project_id' => $projectId
            ]);
            
            return redirect()->back()->with('error', 'Failed to apply learning patterns');
        }
    }
    
    /**
     * Get AI performance statistics
     */
    private function getAiStatistics($projectId = null)
    {
        $query = TimeEntry::whereNotNull('ai_improved_description');
        
        if ($projectId) {
            $query->where('project_id', $projectId);
        } elseif (Auth::user()->role !== 'super_admin') {
            $query->where('company_id', Auth::user()->company_id);
        }
        
        $total = $query->count();
        $reviewed = (clone $query)->whereNotNull('ai_feedback')->count();
        $good = (clone $query)->where('ai_feedback', 'good')->count();
        $bad = (clone $query)->where('ai_feedback', 'bad')->count();
        $adjusted = (clone $query)->where('ai_feedback', 'adjusted')->count();
        $avgConfidence = (clone $query)->avg('ai_confidence') ?? 0;
        
        return [
            'total' => $total,
            'reviewed' => $reviewed,
            'unreviewed' => $total - $reviewed,
            'good' => $good,
            'bad' => $bad,
            'adjusted' => $adjusted,
            'accuracy' => $reviewed > 0 ? round(($good / $reviewed) * 100, 1) : 0,
            'avg_confidence' => round($avgConfidence * 100, 1)
        ];
    }
    
    /**
     * Update project patterns with description improvements
     */
    private function updateProjectPatterns($projectId, $originalDescription, $improvedDescription)
    {
        try {
            $aiSettings = ProjectAiSetting::where('project_id', $projectId)->first();
            if (!$aiSettings) {
                return;
            }
            
            // Create a pattern showing the improvement: "original → improved"
            $pattern = substr($originalDescription, 0, 50) . ' → ' . $improvedDescription;
            
            // Add to example patterns
            $patterns = $aiSettings->ai_example_patterns ?? [];
            
            // Check if a similar pattern already exists
            $exists = false;
            foreach ($patterns as $existingPattern) {
                if (strpos($existingPattern, ' → ' . $improvedDescription) !== false) {
                    $exists = true;
                    break;
                }
            }
            
            if (!$exists) {
                $patterns[] = $pattern;
                // Keep last 30 patterns
                $aiSettings->ai_example_patterns = array_slice($patterns, -30);
                $aiSettings->save();
            }
            
        } catch (\Exception $e) {
            Log::warning('Failed to update project patterns', [
                'error' => $e->getMessage(),
                'project_id' => $projectId
            ]);
        }
    }
}