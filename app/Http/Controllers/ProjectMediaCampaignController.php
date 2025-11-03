<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectMediaCampaign;
use App\Models\ProjectMediaMention;
use App\Models\UserMediaMention;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectMediaCampaignController extends Controller
{
    /**
     * Display campaigns for a project
     */
    public function index(Project $project)
    {
        // Check authorization
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            $isProjectUser = $project->projectUsers()
                ->where('user_id', Auth::id())
                ->exists();
            
            if (!$isProjectUser) {
                abort(403, 'Access denied. You are not assigned to this project.');
            }
        }

        $campaigns = $project->mediaCampaigns()
            ->main() // Only main campaigns, not sub-campaigns
            ->with(['children', 'monitor', 'mentions'])
            ->orderBy('press_release_date', 'desc')
            ->get();

        // Calculate metrics for each campaign
        foreach ($campaigns as $campaign) {
            $campaign->metrics = $campaign->getMetrics();
        }

        // Get unlinked mentions that might belong to this project
        $unlinkedMentions = UserMediaMention::whereNotIn('id', function($query) use ($project) {
            $query->select('user_media_mention_id')
                ->from('project_media_mentions')
                ->where('project_id', $project->id);
        })
        ->where('created_at', '>=', $project->start_date ?? now()->subYear())
        ->orderBy('published_at', 'desc')
        ->limit(50)
        ->get();

        return view('projects.media-campaigns.index', compact('project', 'campaigns', 'unlinkedMentions'));
    }

    /**
     * Show form to create new campaign
     */
    public function create(Project $project)
    {
        // Check authorization
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators and project managers can create campaigns.');
        }

        // Get existing campaigns for parent selection
        $parentCampaigns = $project->mediaCampaigns()->main()->get();

        return view('projects.media-campaigns.create', compact('project', 'parentCampaigns'));
    }

    /**
     * Store new campaign
     */
    public function store(Request $request, Project $project)
    {
        // Check authorization
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'press_release_date' => 'required|date',
            'campaign_type' => 'required|in:product_launch,feature_announcement,company_news,event,partnership,other',
            'parent_id' => 'nullable|exists:project_media_campaigns,id',
            'target_audience' => 'nullable|string',
            'expected_reach' => 'nullable|integer|min:0',
            'keywords' => 'required|string',
            'budget' => 'nullable|numeric|min:0',
            'status' => 'required|in:planning,active,completed,on_hold',
            'notes' => 'nullable|string'
        ]);

        // Convert keywords string to array
        $keywords = array_map('trim', explode(',', $validated['keywords']));
        $validated['keywords'] = $keywords;
        $validated['project_id'] = $project->id;

        DB::beginTransaction();
        try {
            // Create campaign
            $campaign = ProjectMediaCampaign::create($validated);

            // Create associated monitor
            $campaign->syncMonitor();

            // Check existing mentions for matches
            $this->checkExistingMentionsForCampaign($campaign);

            DB::commit();

            return redirect()->route('projects.media-campaigns.show', [$project->id, $campaign->id])
                ->with('success', 'Campaign created successfully and monitor activated.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to create campaign: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Show campaign details
     */
    public function show(Project $project, ProjectMediaCampaign $campaign)
    {
        // Check authorization
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            $isProjectUser = $project->projectUsers()
                ->where('user_id', Auth::id())
                ->exists();
            
            if (!$isProjectUser) {
                abort(403, 'Access denied.');
            }
        }

        // Load relationships
        $campaign->load(['children', 'monitor', 'mentions.userMention']);

        // Get metrics
        $metrics = $campaign->getMetrics();

        // Get mentions timeline data
        $mentionsTimeline = $campaign->mentions()
            ->join('user_media_mentions', 'project_media_mentions.user_media_mention_id', '=', 'user_media_mentions.id')
            ->select(DB::raw('DATE(user_media_mentions.published_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('projects.media-campaigns.show', compact('project', 'campaign', 'metrics', 'mentionsTimeline'));
    }

    /**
     * Show form to edit campaign
     */
    public function edit(Project $project, ProjectMediaCampaign $campaign)
    {
        // Check authorization
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied.');
        }

        $parentCampaigns = $project->mediaCampaigns()
            ->main()
            ->where('id', '!=', $campaign->id)
            ->get();

        return view('projects.media-campaigns.edit', compact('project', 'campaign', 'parentCampaigns'));
    }

    /**
     * Update campaign
     */
    public function update(Request $request, Project $project, ProjectMediaCampaign $campaign)
    {
        // Check authorization
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'press_release_date' => 'required|date',
            'campaign_type' => 'required|in:product_launch,feature_announcement,company_news,event,partnership,other',
            'parent_id' => 'nullable|exists:project_media_campaigns,id',
            'target_audience' => 'nullable|string',
            'expected_reach' => 'nullable|integer|min:0',
            'actual_reach' => 'nullable|integer|min:0',
            'keywords' => 'required|string',
            'budget' => 'nullable|numeric|min:0',
            'actual_cost' => 'nullable|numeric|min:0',
            'status' => 'required|in:planning,active,completed,on_hold',
            'notes' => 'nullable|string'
        ]);

        // Convert keywords string to array
        $keywords = array_map('trim', explode(',', $validated['keywords']));
        $validated['keywords'] = $keywords;

        // Store original keywords for comparison
        $originalKeywords = $campaign->keywords;

        DB::beginTransaction();
        try {
            // Update campaign
            $campaign->update($validated);

            // Force refresh the model to ensure keywords are properly loaded
            $campaign->refresh();

            // Sync monitor (this will update the monitor's keywords too)
            $campaign->syncMonitor();

            // Check if keywords changed by comparing arrays
            $keywordsChanged = !empty(array_diff($keywords, $originalKeywords ?? [])) || 
                              !empty(array_diff($originalKeywords ?? [], $keywords));
            
            if ($keywordsChanged) {
                $this->checkExistingMentionsForCampaign($campaign);
            }

            DB::commit();

            return redirect()->route('projects.media-campaigns.show', [$project->id, $campaign->id])
                ->with('success', 'Campaign updated successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to update campaign: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Delete campaign
     */
    public function destroy(Project $project, ProjectMediaCampaign $campaign)
    {
        // Check authorization
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can delete campaigns.');
        }

        // Check if campaign has children
        if ($campaign->children()->exists()) {
            return back()->withErrors(['error' => 'Cannot delete campaign with sub-campaigns.']);
        }

        DB::beginTransaction();
        try {
            // Delete monitor
            if ($campaign->monitor) {
                $campaign->monitor->delete();
            }

            // Delete campaign (mentions will be cascade deleted)
            $campaign->delete();

            DB::commit();

            return redirect()->route('projects.media-campaigns.index', $project->id)
                ->with('success', 'Campaign deleted successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to delete campaign: ' . $e->getMessage()]);
        }
    }

    /**
     * Link a mention to a campaign
     */
    public function linkMention(Request $request, Project $project, ProjectMediaCampaign $campaign)
    {
        // Check authorization
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied.');
        }

        $validated = $request->validate([
            'mention_id' => 'required|exists:user_media_mentions,id',
            'confidence_score' => 'nullable|integer|min:0|max:100',
            'notes' => 'nullable|string'
        ]);

        // Check if already linked
        $existing = ProjectMediaMention::where('user_media_mention_id', $validated['mention_id'])
            ->where('project_id', $project->id)
            ->first();

        if ($existing) {
            return back()->withErrors(['error' => 'This mention is already linked to this project.']);
        }

        // Create link
        ProjectMediaMention::create([
            'project_id' => $project->id,
            'campaign_id' => $campaign->id,
            'user_media_mention_id' => $validated['mention_id'],
            'assigned_by' => Auth::id(),
            'assignment_method' => 'manual',
            'confidence_score' => $validated['confidence_score'] ?? 100,
            'notes' => $validated['notes'] ?? null
        ]);

        return back()->with('success', 'Mention linked to campaign successfully.');
    }

    /**
     * Unlink a mention from a campaign
     */
    public function unlinkMention(Project $project, ProjectMediaCampaign $campaign, $mentionId)
    {
        // Check authorization
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied.');
        }

        $mention = ProjectMediaMention::where('project_id', $project->id)
            ->where('campaign_id', $campaign->id)
            ->where('user_media_mention_id', $mentionId)
            ->firstOrFail();

        $mention->delete();

        return back()->with('success', 'Mention unlinked from campaign successfully.');
    }

    /**
     * Check existing mentions for campaign matches
     */
    private function checkExistingMentionsForCampaign(ProjectMediaCampaign $campaign)
    {
        // Get mentions from last 30 days that aren't already linked to this project
        $recentMentions = UserMediaMention::where('published_at', '>=', now()->subDays(30))
            ->whereNotIn('id', function($query) use ($campaign) {
                $query->select('user_media_mention_id')
                    ->from('project_media_mentions')
                    ->where('project_id', $campaign->project_id);
            })
            ->get();

        foreach ($recentMentions as $mention) {
            // Check if any keywords match
            $content = $mention->article_title . ' ' . $mention->article_excerpt;
            $matches = false;
            $score = 0;

            foreach ($campaign->keywords as $keyword) {
                if (stripos($content, $keyword) !== false) {
                    $matches = true;
                    $score += 20;
                }
            }

            if ($matches) {
                // Auto-link to campaign
                ProjectMediaMention::create([
                    'project_id' => $campaign->project_id,
                    'campaign_id' => $campaign->id,
                    'user_media_mention_id' => $mention->id,
                    'assigned_by' => Auth::id(),
                    'assignment_method' => 'automatic',
                    'confidence_score' => min($score, 100)
                ]);
            }
        }
    }
}