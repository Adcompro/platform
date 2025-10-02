<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing AI settings to focus on descriptions instead of subtasks
        $settings = DB::table('project_ai_settings')->get();
        
        foreach ($settings as $setting) {
            $updated = false;
            $changes = [];
            
            // Update naming rules if they mention subtasks
            if (str_contains($setting->ai_naming_rules ?? '', 'subtask')) {
                $changes['ai_naming_rules'] = '- Focus on creating CONSISTENT and CLEAR descriptions
- Keep descriptions concise but informative (max 100 characters)
- Use consistent terminology throughout the project
- Learn from recent entries and follow existing patterns
- Standardize similar activities into consistent descriptions
- Be specific about what was done but avoid unnecessary details';
                $updated = true;
            }
            
            // Update example patterns to be full descriptions instead of "Category - Action" format
            $examplePatterns = json_decode($setting->ai_example_patterns ?? '[]', true);
            if (!empty($examplePatterns)) {
                $newPatterns = [];
                foreach ($examplePatterns as $pattern) {
                    // Check if pattern is in old "Category - Action" format
                    if (strpos($pattern, ' - ') !== false && strpos($pattern, ' → ') === false) {
                        // Convert "API - Fix authentication bug" to "Fixed authentication bug in API"
                        $parts = explode(' - ', $pattern, 2);
                        if (count($parts) == 2) {
                            $category = trim($parts[0]);
                            $action = trim($parts[1]);
                            
                            // Create a more natural description
                            if (stripos($action, 'fix') === 0) {
                                $newPatterns[] = ucfirst($action) . ' in ' . strtolower($category);
                            } elseif (stripos($action, 'update') === 0 || stripos($action, 'create') === 0) {
                                $newPatterns[] = ucfirst($action) . ' for ' . strtolower($category);
                            } else {
                                $newPatterns[] = $category . ': ' . $action;
                            }
                        } else {
                            $newPatterns[] = $pattern;
                        }
                    } else {
                        // Keep patterns that are already in the correct format or are learning patterns (with →)
                        $newPatterns[] = $pattern;
                    }
                }
                
                // Add some good default examples if we don't have many
                if (count($newPatterns) < 5) {
                    $defaults = [
                        'Fixed authentication bug in login API',
                        'Optimized database queries for dashboard',
                        'Updated user interface layout for mobile view',
                        'Attended sprint planning meeting',
                        'Updated API documentation for v2 endpoints',
                        'Created unit tests for user authentication',
                        'Deployed hotfix to production server',
                        'Reviewed and merged pull request',
                        'Resolved customer support ticket',
                        'Implemented new feature for data export'
                    ];
                    
                    foreach ($defaults as $default) {
                        if (!in_array($default, $newPatterns) && count($newPatterns) < 10) {
                            $newPatterns[] = $default;
                        }
                    }
                }
                
                $changes['ai_example_patterns'] = json_encode($newPatterns);
                $updated = true;
            }
            
            // Update the record if changes were made
            if ($updated) {
                DB::table('project_ai_settings')
                    ->where('id', $setting->id)
                    ->update($changes);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration updates data, no structural changes to reverse
    }
};