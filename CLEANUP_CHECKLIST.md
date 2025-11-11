# QUICK CLEANUP CHECKLIST
## Progress Management Platform

**Date:** 2025-11-11  
**Complexity:** Medium  
**Estimated Time:** 2-4 hours (full cleanup)

---

## PHASE 1: IMMEDIATE CLEANUP (15 mins - No Risk)

### Backup Files to Delete
```bash
# Run these commands to delete backup files safely
rm /var/www/vhosts/adcompro.app/progress.adcompro.app/resources/views/projects/edit-test.blade.php
rm /var/www/vhosts/adcompro.app/progress.adcompro.app/resources/views/projects/test-simple-form.blade.php
rm /var/www/vhosts/adcompro.app/progress.adcompro.app/resources/views/projects/edit-final.blade.php
rm /var/www/vhosts/adcompro.app/progress.adcompro.app/resources/views/projects/edit-clean-script.blade.php
rm /var/www/vhosts/adcompro.app/progress.adcompro.app/resources/views/projects/edit-simplified.blade.php
rm /var/www/vhosts/adcompro.app/progress.adcompro.app/resources/views/projects/edit-new.blade.php
rm /var/www/vhosts/adcompro.app/progress.adcompro.app/resources/views/projects/edit-broken.blade.php
rm /var/www/vhosts/adcompro.app/progress.adcompro.app/resources/views/projects/edit-backup.blade.php
rm /var/www/vhosts/adcompro.app/progress.adcompro.app/resources/views/companies/index_old.blade.php
rm /var/www/vhosts/adcompro.app/progress.adcompro.app/resources/views/projects/index.blade.php.bak_help
rm /var/www/vhosts/adcompro.app/progress.adcompro.app/resources/views/projects/show-help-section.tmp
```

### Disable Unused Route (Optional)
In `/routes/web.php` line 446, the ai-usage route is already commented:
```php
// Route::get('settings/ai-usage', [SettingsController::class, 'aiUsage'])->name('settings.ai-usage');
// Delete ai-usage.blade.php view file:
rm /var/www/vhosts/adcompro.app/progress.adcompro.app/resources/views/settings/ai-usage.blade.php
```

---

## PHASE 2: MEDIUM PRIORITY (2-4 hours - Needs Verification)

### Option A: Delete AI Features (If Not Used)

#### 1. Remove AI Learning Module
```bash
# Delete controller
rm app/Http/Controllers/AiLearningController.php

# Delete model
rm app/Models/AiLearningFeedback.php

# Delete views
rm -rf resources/views/ai-learning/

# Delete routes in routes/web.php (lines 264-267):
# Route::get('ai-learning', [AiLearningController::class, 'index'])->name('ai-learning.index');
# Route::post('ai-learning/feedback/{timeEntry}', [AiLearningController::class, 'updateFeedback'])->name('ai-learning.feedback');
# Route::post('ai-learning/bulk-review', [AiLearningController::class, 'bulkReview'])->name('ai-learning.bulk-review');
# Route::post('ai-learning/apply/{project}', [AiLearningController::class, 'applyLearning'])->name('ai-learning.apply');
```

#### 2. Remove AI Digest Module
```bash
# Delete controller
rm app/Http/Controllers/AIDigestController.php

# Delete views
rm -rf resources/views/ai-digest/
rm resources/views/emails/weekly-digest.blade.php

# Delete routes in routes/web.php (lines 433-439):
# Route::prefix('ai-digest')->group(function () {
#     Route::get('/', [AIDigestController::class, 'index'])->name('ai-digest.index');
#     Route::put('/settings', [AIDigestController::class, 'updateSettings'])->name('ai-digest.settings');
#     Route::post('/generate', [AIDigestController::class, 'generate'])->name('ai-digest.generate');
#     Route::get('/preview', [AIDigestController::class, 'preview'])->name('ai-digest.preview');
#     Route::get('/download', [AIDigestController::class, 'download'])->name('ai-digest.download');
# });
```

### Option B: Fix Time Entry Import (If Using)

**Status:** Menu item visible but broken (no views)

#### Either: Implement Missing Views
```bash
# Create view directory
mkdir -p resources/views/time-entries/import

# Create the missing view files:
touch resources/views/time-entries/import/index.blade.php
touch resources/views/time-entries/import/preview.blade.php
touch resources/views/time-entries/import/result.blade.php

# Then populate with proper forms and previews
```

#### Or: Remove Time Entry Import
```bash
# Delete controller
rm app/Http/Controllers/TimeEntryImportController.php

# Delete routes in routes/web.php (lines 248-252):
# Route::get('time-entries/import', [\App\Http\Controllers\TimeEntryImportController::class, 'index'])->name('time-entries.import.index');
# Route::post('time-entries/import/upload', [\App\Http\Controllers\TimeEntryImportController::class, 'upload'])->name('time-entries.import.upload');
# Route::get('time-entries/import/preview', [\App\Http\Controllers\TimeEntryImportController::class, 'preview'])->name('time-entries.import.preview');
# Route::post('time-entries/import/import', [\App\Http\Controllers\TimeEntryImportController::class, 'import'])->name('time-entries.import.import');
# Route::post('time-entries/import/cancel', [\App\Http\Controllers\TimeEntryImportController::class, 'cancel'])->name('time-entries.import.cancel');

# Remove from menu in app.blade.php (lines 672-676)
```

### Option C: Delete Unused Models
```bash
# If RSS monitoring not needed:
rm app/Models/RssFeedCache.php
rm app/Console/Commands/CollectRssFeeds.php
rm app/Jobs/AnalyzeMediaMention.php

# If social metrics not needed:
rm app/Models/SocialEngagementMetric.php
rm app/Models/UserSocialMention.php
```

---

## PHASE 3: DESIGN DECISION REQUIRED (Low Priority)

### Decision 1: Project Media Campaigns
**Current Status:** Routes + Views exist, no menu access

```bash
# Option A: Add menu item to project view
# Edit resources/views/projects/show.blade.php
# Add link to route('projects.media-campaigns.index', $project->id)

# Option B: Delete if not needed
rm app/Http/Controllers/ProjectMediaCampaignController.php
rm app/Models/ProjectMediaCampaign.php
rm app/Models/ProjectMediaMention.php
rm app/Models/ProjectSocialMention.php
rm -rf resources/views/projects/media-campaigns/
# Delete routes in routes/web.php (lines 298-308)
```

### Decision 2: Multiple Calendar Providers
**Current Status:** Routes exist for Google, Apple, Microsoft providers

```bash
# Option A: Keep for multi-provider support (no action needed)

# Option B: Delete if only Microsoft 365 is used
rm app/Http/Controllers/MultiCalendarController.php
# Delete routes in routes/web.php (lines 389-413)
# Also remove views in resources/views/calendar/providers/
```

### Decision 3: Services Module
**Current Status:** Complete code, commented out in menu

```bash
# Option A: Re-enable services
# Uncomment in resources/views/layouts/app.blade.php (lines 631-636)
# Add resource route to routes/web.php:
# Route::resource('services', ServiceController::class);
# Route::resource('service-milestones', ServiceMilestoneController::class);
# Route::resource('service-tasks', ServiceTaskController::class);
# Route::resource('service-subtasks', ServiceSubtaskController::class);

# Option B: Delete services module
rm app/Http/Controllers/ServiceController.php
rm app/Http/Controllers/ServiceMilestoneController.php
rm app/Http/Controllers/ServiceTaskController.php
rm app/Http/Controllers/ServiceSubtaskController.php
rm app/Models/Service.php
rm app/Models/ServiceMilestone.php
rm app/Models/ServiceTask.php
rm app/Models/ServiceSubtask.php
rm app/Models/ServiceActivity.php
rm -rf resources/views/services/
```

---

## VERIFICATION AFTER CLEANUP

```bash
# 1. Test application loads
php artisan serve

# 2. Check for any broken references
grep -r "AiLearning\|AIDigest\|TimeEntryImport" app/Http/Controllers --include="*.php"

# 3. Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# 4. Run tests (if available)
php artisan test

# 5. Check git status
git status
```

---

## GIT WORKFLOW

```bash
# 1. Create feature branch
git checkout -b cleanup/remove-orphaned-features

# 2. Stage changes
git add .

# 3. Commit with detailed message
git commit -m "chore: remove orphaned features (Phase 1-3)

- Remove backup and test view files
- Delete AI Learning module (routes, controller, model, views)
- Delete AI Digest module (routes, controller, views, email)
- Remove unused database models
- Clean up incomplete implementations

See ORPHANED_FEATURES_ANALYSIS.md for details"

# 4. Push to remote
git push origin cleanup/remove-orphaned-features

# 5. Create PR and review before merging
```

---

## SUMMARY

| Phase | Items | Risk | Time | Status |
|-------|-------|------|------|--------|
| 1 | 11 files | NONE | 15m | [ ] TODO |
| 2 | 6 features | LOW | 2-4h | [ ] TODO |
| 3 | 3 decisions | MEDIUM | 1-2h | [ ] TODO |

**Total Estimated Time:** 3.5-6.5 hours

---

## NOTES

- Always backup database before cleanup
- Test thoroughly after each phase
- Run full test suite if available
- Verify no external integrations depend on deleted features
- Document any custom changes to controllers before deletion

---

**For detailed analysis, see:** `/ORPHANED_FEATURES_ANALYSIS.md`
