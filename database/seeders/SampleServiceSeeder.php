<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceMilestone;
use App\Models\ServiceTask;
use App\Models\ServiceSubtask;
use App\Models\Company;

class SampleServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first company for service assignment
        $company = Company::first();
        
        if (!$company) {
            $this->command->error('No company found. Please seed companies first.');
            return;
        }

        // Create or get service category
        $category = ServiceCategory::firstOrCreate(
            ['name' => 'Web Development'],
            [
                'company_id' => $company->id,
                'description' => 'Web development and design services',
                'color' => '#3B82F6',
                'icon' => 'globe',
                'is_active' => true,
                'sort_order' => 1
            ]
        );

        // Create Webdesign Service
        $webdesignService = Service::create([
            'service_category_id' => $category->id,
            'company_id' => $company->id,
            'name' => 'Complete Webdesign Package',
            'description' => 'Full website design and development service including responsive design, CMS integration, and SEO optimization',
            'sku_code' => 'WEB-DESIGN-001',
            'total_price' => 4500.00,
            'estimated_hours' => 60,
            'is_package' => true,
            'is_active' => true,
            'is_public' => true,
            'status' => 'active',
            'created_by' => 1,
            'updated_by' => 1,
        ]);

        // Create milestones for webdesign service
        $designMilestone = ServiceMilestone::create([
            'service_id' => $webdesignService->id,
            'name' => 'Design Phase',
            'description' => 'Complete design including mockups and prototypes',
            'estimated_hours' => 20,
            'sort_order' => 1,
            'included_in_price' => true
        ]);

        // Tasks for design milestone
        $wireframeTask = ServiceTask::create([
            'service_milestone_id' => $designMilestone->id,
            'name' => 'Wireframe Creation',
            'description' => 'Create wireframes for all pages',
            'estimated_hours' => 8,
            'sort_order' => 1,
            'included_in_price' => true
        ]);

        // Subtasks for wireframe task
        ServiceSubtask::create([
            'service_task_id' => $wireframeTask->id,
            'name' => 'Homepage Wireframe',
            'description' => 'Design homepage layout and structure',
            'estimated_hours' => 3,
            'sort_order' => 1,
            'included_in_price' => true
        ]);

        ServiceSubtask::create([
            'service_task_id' => $wireframeTask->id,
            'name' => 'Inner Pages Wireframes',
            'description' => 'Design layouts for inner pages',
            'estimated_hours' => 5,
            'sort_order' => 2,
            'included_in_price' => true
        ]);

        $visualDesignTask = ServiceTask::create([
            'service_milestone_id' => $designMilestone->id,
            'name' => 'Visual Design',
            'description' => 'Create visual designs based on wireframes',
            'estimated_hours' => 12,
            'sort_order' => 2,
            'included_in_price' => true
        ]);

        // Development milestone
        $developmentMilestone = ServiceMilestone::create([
            'service_id' => $webdesignService->id,
            'name' => 'Development Phase',
            'description' => 'Frontend and backend development',
            'estimated_hours' => 30,
            'sort_order' => 2,
            'included_in_price' => true
        ]);

        $frontendTask = ServiceTask::create([
            'service_milestone_id' => $developmentMilestone->id,
            'name' => 'Frontend Development',
            'description' => 'HTML, CSS, JavaScript implementation',
            'estimated_hours' => 18,
            'sort_order' => 1,
            'included_in_price' => true
        ]);

        $backendTask = ServiceTask::create([
            'service_milestone_id' => $developmentMilestone->id,
            'name' => 'CMS Integration',
            'description' => 'Content Management System setup',
            'estimated_hours' => 12,
            'sort_order' => 2,
            'included_in_price' => true
        ]);

        // Launch milestone
        $launchMilestone = ServiceMilestone::create([
            'service_id' => $webdesignService->id,
            'name' => 'Launch & Optimization',
            'description' => 'Testing, SEO, and launch',
            'estimated_hours' => 10,
            'sort_order' => 3,
            'included_in_price' => true
        ]);

        $testingTask = ServiceTask::create([
            'service_milestone_id' => $launchMilestone->id,
            'name' => 'Testing & QA',
            'description' => 'Cross-browser testing and bug fixes',
            'estimated_hours' => 5,
            'sort_order' => 1,
            'included_in_price' => true
        ]);

        $seoTask = ServiceTask::create([
            'service_milestone_id' => $launchMilestone->id,
            'name' => 'SEO Optimization',
            'description' => 'Basic SEO setup and optimization',
            'estimated_hours' => 5,
            'sort_order' => 2,
            'included_in_price' => true
        ]);

        // Create second service: Logo Design
        $logoService = Service::create([
            'service_category_id' => $category->id,
            'company_id' => $company->id,
            'name' => 'Professional Logo Design',
            'description' => 'Complete logo design package with multiple concepts and revisions',
            'sku_code' => 'LOGO-DESIGN-001',
            'total_price' => 850.00,
            'estimated_hours' => 12,
            'is_package' => false,
            'is_active' => true,
            'is_public' => true,
            'status' => 'active',
            'created_by' => 1,
            'updated_by' => 1,
        ]);

        $logoMilestone = ServiceMilestone::create([
            'service_id' => $logoService->id,
            'name' => 'Logo Design Process',
            'description' => 'Complete logo design with revisions',
            'estimated_hours' => 12,
            'sort_order' => 1,
            'included_in_price' => true
        ]);

        ServiceTask::create([
            'service_milestone_id' => $logoMilestone->id,
            'name' => 'Initial Concepts',
            'description' => 'Create 3 initial logo concepts',
            'estimated_hours' => 6,
            'sort_order' => 1,
            'included_in_price' => true
        ]);

        ServiceTask::create([
            'service_milestone_id' => $logoMilestone->id,
            'name' => 'Revisions & Finalization',
            'description' => 'Refine selected concept and deliver final files',
            'estimated_hours' => 6,
            'sort_order' => 2,
            'included_in_price' => true
        ]);

        $this->command->info('Created 2 sample services with complete structure:');
        $this->command->info('- Complete Webdesign Package (€4,500)');
        $this->command->info('- Professional Logo Design (€850)');
    }
}