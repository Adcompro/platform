<?php

namespace App\Services;

use App\Models\CalendarEvent;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\ProjectTask;
use App\Models\ProjectSubtask;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AICalendarPredictionService
{
    private $claudeService;
    
    public function __construct(ClaudeAIService $claudeService)
    {
        $this->claudeService = $claudeService;
    }
    
    /**
     * Predict project and work items for a calendar event
     */
    public function predictProjectAndWorkItems(CalendarEvent $event)
    {
        // Try to get from cache first (cache for 1 hour)
        $cacheKey = "calendar_prediction_{$event->id}_" . Auth::id();
        
        return Cache::remember($cacheKey, 3600, function() use ($event) {
            // Get user's recent time entries for pattern analysis
            $recentEntries = TimeEntry::with(['project', 'milestone', 'task', 'subtask'])
                ->where('user_id', Auth::id())
                ->where('entry_date', '>=', now()->subMonths(3))
                ->orderBy('entry_date', 'desc')
                ->limit(100)
                ->get();
            
            // Get active projects for the user
            $activeProjects = Project::whereHas('users', function($q) {
                $q->where('user_id', Auth::id());
            })
            ->where('status', 'active')
            ->with(['milestones.tasks.subtasks', 'customer'])
            ->get();
            
            // Analyze patterns from historical data
            $patterns = $this->analyzePatterns($recentEntries, $event);
            
            // Use AI to predict based on event details
            $aiPrediction = $this->getAIPrediction($event, $activeProjects, $patterns);
            
            // Combine pattern analysis with AI prediction
            return $this->combinePredictions($patterns, $aiPrediction, $activeProjects);
        });
    }
    
    /**
     * Analyze patterns from historical time entries
     */
    private function analyzePatterns($recentEntries, CalendarEvent $event)
    {
        $patterns = [
            'projects' => [],
            'milestones' => [],
            'tasks' => [],
            'subtasks' => []
        ];
        
        // Extract keywords from event
        $eventKeywords = $this->extractKeywords($event->subject . ' ' . $event->body);
        
        // Analyze attendees
        $attendeeEmails = [];
        if ($event->attendees) {
            $attendees = is_string($event->attendees) ? json_decode($event->attendees, true) : $event->attendees;
            if (is_array($attendees)) {
                foreach ($attendees as $attendee) {
                    if (isset($attendee['email'])) {
                        $attendeeEmails[] = $attendee['email'];
                    }
                }
            }
        }
        
        // Find patterns in recent entries
        foreach ($recentEntries as $entry) {
            $entryKeywords = $this->extractKeywords($entry->description);
            $similarity = $this->calculateSimilarity($eventKeywords, $entryKeywords);
            
            // Check if similar time of day
            $eventHour = $event->start_datetime->format('H');
            $entryHour = $entry->created_at->format('H');
            $timeSimilarity = abs($eventHour - $entryHour) < 2 ? 0.1 : 0;
            
            $totalScore = $similarity + $timeSimilarity;
            
            if ($totalScore > 0.3) {
                // Track project patterns
                if ($entry->project) {
                    if (!isset($patterns['projects'][$entry->project_id])) {
                        $patterns['projects'][$entry->project_id] = [
                            'project' => $entry->project,
                            'score' => 0,
                            'count' => 0
                        ];
                    }
                    $patterns['projects'][$entry->project_id]['score'] += $totalScore;
                    $patterns['projects'][$entry->project_id]['count']++;
                }
                
                // Track milestone patterns
                if ($entry->milestone) {
                    if (!isset($patterns['milestones'][$entry->project_milestone_id])) {
                        $patterns['milestones'][$entry->project_milestone_id] = [
                            'milestone' => $entry->milestone,
                            'score' => 0,
                            'count' => 0
                        ];
                    }
                    $patterns['milestones'][$entry->project_milestone_id]['score'] += $totalScore;
                    $patterns['milestones'][$entry->project_milestone_id]['count']++;
                }
                
                // Track task patterns
                if ($entry->task) {
                    if (!isset($patterns['tasks'][$entry->project_task_id])) {
                        $patterns['tasks'][$entry->project_task_id] = [
                            'task' => $entry->task,
                            'score' => 0,
                            'count' => 0
                        ];
                    }
                    $patterns['tasks'][$entry->project_task_id]['score'] += $totalScore;
                    $patterns['tasks'][$entry->project_task_id]['count']++;
                }
                
                // Track subtask patterns
                if ($entry->subtask) {
                    if (!isset($patterns['subtasks'][$entry->project_subtask_id])) {
                        $patterns['subtasks'][$entry->project_subtask_id] = [
                            'subtask' => $entry->subtask,
                            'score' => 0,
                            'count' => 0
                        ];
                    }
                    $patterns['subtasks'][$entry->project_subtask_id]['score'] += $totalScore;
                    $patterns['subtasks'][$entry->project_subtask_id]['count']++;
                }
            }
        }
        
        // Sort patterns by score
        foreach (['projects', 'milestones', 'tasks', 'subtasks'] as $type) {
            uasort($patterns[$type], function($a, $b) {
                return $b['score'] <=> $a['score'];
            });
        }
        
        return $patterns;
    }
    
    /**
     * Get AI prediction for project and work items
     */
    private function getAIPrediction(CalendarEvent $event, $activeProjects, $patterns)
    {
        // Prepare event data for AI
        $eventData = [
            'subject' => $event->subject,
            'body' => strip_tags($event->body),
            'location' => $event->location,
            'attendees' => [],
            'duration_minutes' => $event->start_datetime->diffInMinutes($event->end_datetime),
            'time_of_day' => $event->start_datetime->format('H:i'),
            'day_of_week' => $event->start_datetime->format('l')
        ];
        
        // Parse attendees
        if ($event->attendees) {
            $attendees = is_string($event->attendees) ? json_decode($event->attendees, true) : $event->attendees;
            if (is_array($attendees)) {
                foreach ($attendees as $attendee) {
                    $eventData['attendees'][] = [
                        'name' => $attendee['name'] ?? '',
                        'email' => $attendee['email'] ?? ''
                    ];
                }
            }
        }
        
        // Prepare projects data for AI
        $projectsData = [];
        foreach ($activeProjects as $project) {
            $projectInfo = [
                'id' => $project->id,
                'name' => $project->name,
                'customer' => $project->customer ? $project->customer->name : null,
                'milestones' => []
            ];
            
            foreach ($project->milestones as $milestone) {
                $milestoneInfo = [
                    'id' => $milestone->id,
                    'name' => $milestone->name,
                    'tasks' => []
                ];
                
                foreach ($milestone->tasks as $task) {
                    $taskInfo = [
                        'id' => $task->id,
                        'name' => $task->name,
                        'subtasks' => []
                    ];
                    
                    foreach ($task->subtasks as $subtask) {
                        $taskInfo['subtasks'][] = [
                            'id' => $subtask->id,
                            'name' => $subtask->name
                        ];
                    }
                    
                    $milestoneInfo['tasks'][] = $taskInfo;
                }
                
                $projectInfo['milestones'][] = $milestoneInfo;
            }
            
            $projectsData[] = $projectInfo;
        }
        
        // Get top patterns for context
        $topPatterns = [
            'recent_projects' => array_slice(array_keys($patterns['projects']), 0, 3),
            'recent_milestones' => array_slice(array_map(function($item) {
                return $item['milestone']->name ?? '';
            }, $patterns['milestones']), 0, 3),
            'recent_tasks' => array_slice(array_map(function($item) {
                return $item['task']->name ?? '';
            }, $patterns['tasks']), 0, 3)
        ];
        
        $prompt = "You are an AI assistant helping to predict the correct project and work items for a calendar event that needs to be converted to a time entry.

Calendar Event Details:
- Subject: {$eventData['subject']}
- Description: {$eventData['body']}
- Location: {$eventData['location']}
- Duration: {$eventData['duration_minutes']} minutes
- Time: {$eventData['time_of_day']} on {$eventData['day_of_week']}
- Attendees: " . json_encode($eventData['attendees']) . "

Recent Work Patterns:
- Recent Projects: " . implode(', ', $topPatterns['recent_projects']) . "
- Recent Milestones: " . implode(', ', $topPatterns['recent_milestones']) . "
- Recent Tasks: " . implode(', ', $topPatterns['recent_tasks']) . "

Available Projects and Structure:
" . json_encode($projectsData, JSON_PRETTY_PRINT) . "

Based on the event details, attendees, and work patterns, predict:
1. The most likely project ID
2. The most likely milestone ID (if applicable)
3. The most likely task ID (if applicable)
4. The most likely subtask ID (if applicable)
5. A confidence score (0-100) for each prediction
6. A suggested description for the time entry

Consider:
- Keywords in the event subject/body that match project/task names
- Attendee names/emails that might indicate specific customers or projects
- Meeting types (standup, review, planning) that suggest specific work items
- Location information that might indicate project context

Return your response as JSON with this structure:
{
    \"project_id\": <id or null>,
    \"project_confidence\": <0-100>,
    \"milestone_id\": <id or null>,
    \"milestone_confidence\": <0-100>,
    \"task_id\": <id or null>,
    \"task_confidence\": <0-100>,
    \"subtask_id\": <id or null>,
    \"subtask_confidence\": <0-100>,
    \"suggested_description\": \"<description>\",
    \"reasoning\": \"<brief explanation of predictions>\"
}";
        
        try {
            $response = $this->claudeService->sendRequest($prompt, 'calendar_prediction');
            
            // Parse the JSON response
            $prediction = json_decode($response, true);
            if (!$prediction) {
                throw new \Exception('Invalid JSON response from AI');
            }
            
            return $prediction;
        } catch (\Exception $e) {
            \Log::warning('AI Calendar Prediction failed', ['error' => $e->getMessage()]);
            return null;
        }
    }
    
    /**
     * Combine pattern analysis with AI prediction
     */
    private function combinePredictions($patterns, $aiPrediction, $activeProjects)
    {
        $result = [
            'project_id' => null,
            'milestone_id' => null,
            'task_id' => null,
            'subtask_id' => null,
            'confidence' => 0,
            'description' => '',
            'source' => 'none'
        ];
        
        // If we have both patterns and AI prediction, combine them
        if ($aiPrediction && !empty($patterns['projects'])) {
            // Weight: 60% AI, 40% patterns
            $aiWeight = 0.6;
            $patternWeight = 0.4;
            
            // Get top pattern project
            $topPatternProject = reset($patterns['projects']);
            $patternProjectId = $topPatternProject ? key($patterns['projects']) : null;
            
            // Decide on project
            if ($aiPrediction['project_confidence'] > 70) {
                $result['project_id'] = $aiPrediction['project_id'];
                $result['source'] = 'ai';
            } elseif ($patternProjectId && $topPatternProject['score'] > 1) {
                $result['project_id'] = $patternProjectId;
                $result['source'] = 'pattern';
            } elseif ($aiPrediction['project_id']) {
                $result['project_id'] = $aiPrediction['project_id'];
                $result['source'] = 'ai';
            }
            
            // Use AI predictions for work items if project matches
            if ($result['project_id'] == $aiPrediction['project_id']) {
                $result['milestone_id'] = $aiPrediction['milestone_id'];
                $result['task_id'] = $aiPrediction['task_id'];
                $result['subtask_id'] = $aiPrediction['subtask_id'];
                $result['confidence'] = $aiPrediction['project_confidence'];
            }
            
            $result['description'] = $aiPrediction['suggested_description'] ?? '';
            
        } elseif ($aiPrediction) {
            // Only AI prediction available
            $result['project_id'] = $aiPrediction['project_id'];
            $result['milestone_id'] = $aiPrediction['milestone_id'];
            $result['task_id'] = $aiPrediction['task_id'];
            $result['subtask_id'] = $aiPrediction['subtask_id'];
            $result['confidence'] = $aiPrediction['project_confidence'] ?? 0;
            $result['description'] = $aiPrediction['suggested_description'] ?? '';
            $result['source'] = 'ai';
            
        } elseif (!empty($patterns['projects'])) {
            // Only patterns available
            $topProject = reset($patterns['projects']);
            if ($topProject && $topProject['score'] > 0.5) {
                $result['project_id'] = key($patterns['projects']);
                $result['confidence'] = min(round($topProject['score'] * 50), 100);
                $result['source'] = 'pattern';
                
                // Try to get milestone from patterns
                if (!empty($patterns['milestones'])) {
                    $topMilestone = reset($patterns['milestones']);
                    if ($topMilestone && $topMilestone['milestone']->project_id == $result['project_id']) {
                        $result['milestone_id'] = key($patterns['milestones']);
                    }
                }
                
                // Try to get task from patterns
                if (!empty($patterns['tasks']) && $result['milestone_id']) {
                    $topTask = reset($patterns['tasks']);
                    if ($topTask && $topTask['task']->project_milestone_id == $result['milestone_id']) {
                        $result['task_id'] = key($patterns['tasks']);
                    }
                }
                
                // Try to get subtask from patterns
                if (!empty($patterns['subtasks']) && $result['task_id']) {
                    $topSubtask = reset($patterns['subtasks']);
                    if ($topSubtask && $topSubtask['subtask']->project_task_id == $result['task_id']) {
                        $result['subtask_id'] = key($patterns['subtasks']);
                    }
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Extract keywords from text
     */
    private function extractKeywords($text)
    {
        // Convert to lowercase and remove special characters
        $text = strtolower(preg_replace('/[^a-zA-Z0-9\s]/', ' ', $text));
        
        // Split into words
        $words = explode(' ', $text);
        
        // Remove common stop words
        $stopWords = ['the', 'is', 'at', 'which', 'on', 'a', 'an', 'as', 'are', 'was', 'were', 'been', 'be', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might', 'must', 'can', 'could', 'to', 'of', 'in', 'for', 'with', 'about', 'into', 'through', 'during', 'before', 'after', 'above', 'below', 'from', 'up', 'down', 'out', 'off', 'over', 'under', 'again', 'further', 'then', 'once'];
        
        $keywords = array_filter($words, function($word) use ($stopWords) {
            return strlen($word) > 2 && !in_array($word, $stopWords);
        });
        
        return array_values($keywords);
    }
    
    /**
     * Calculate similarity between two sets of keywords
     */
    private function calculateSimilarity($keywords1, $keywords2)
    {
        if (empty($keywords1) || empty($keywords2)) {
            return 0;
        }
        
        $intersection = array_intersect($keywords1, $keywords2);
        $union = array_unique(array_merge($keywords1, $keywords2));
        
        if (count($union) == 0) {
            return 0;
        }
        
        return count($intersection) / count($union);
    }
}