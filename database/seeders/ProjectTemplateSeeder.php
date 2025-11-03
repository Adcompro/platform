<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use App\Models\ProjectTemplate;
use App\Models\TemplateMilestone;
use App\Models\TemplateTask;
use App\Models\TemplateSubtask;
use App\Models\Company;
use App\Models\User;

class ProjectTemplateSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Get first company and user for template ownership
        $company = Company::first();
        $user = User::first();

        $templates = [
            [
                'name' => 'E-commerce Website',
                'category' => 'website',
                'description' => 'Complete e-commerce website met product catalog, shopping cart, checkout en admin panel',
                'status' => 'active',
                'milestones' => [
                    [
                        'name' => 'Project Setup & Planning',
                        'description' => 'Initial setup, requirements gathering en project planning',
                        'estimated_hours' => 16,
                        'default_hourly_rate' => 95.00,
                        'days_from_start' => 0,
                        'duration_days' => 3,
                        'tasks' => [
                            [
                                'name' => 'Requirements Analysis',
                                'description' => 'Detailed requirements gathering en stakeholder interviews',
                                'estimated_hours' => 8,
                                'subtasks' => [
                                    ['name' => 'Stakeholder interviews', 'estimated_hours' => 4],
                                    ['name' => 'Technical requirements document', 'estimated_hours' => 2],
                                    ['name' => 'User stories creation', 'estimated_hours' => 2],
                                ]
                            ],
                            [
                                'name' => 'Project Setup',
                                'description' => 'Development environment en project structure setup',
                                'estimated_hours' => 6,
                                'subtasks' => [
                                    ['name' => 'Git repository setup', 'estimated_hours' => 1],
                                    ['name' => 'Development environment', 'estimated_hours' => 3],
                                    ['name' => 'CI/CD pipeline setup', 'estimated_hours' => 2],
                                ]
                            ],
                        ]
                    ],
                    [
                        'name' => 'Frontend Development',
                        'description' => 'Frontend development van alle user-facing components',
                        'estimated_hours' => 80,
                        'default_hourly_rate' => 80.00,
                        'days_from_start' => 5,
                        'duration_days' => 20,
                        'tasks' => [
                            [
                                'name' => 'Homepage Development',
                                'description' => 'Complete homepage met hero section, featured products',
                                'estimated_hours' => 20,
                                'subtasks' => [
                                    ['name' => 'Hero section', 'estimated_hours' => 6],
                                    ['name' => 'Featured products grid', 'estimated_hours' => 8],
                                    ['name' => 'Newsletter signup', 'estimated_hours' => 4],
                                ]
                            ],
                            [
                                'name' => 'Product Catalog',
                                'description' => 'Product listing, filtering, zoeken en product detail pages',
                                'estimated_hours' => 35,
                                'subtasks' => [
                                    ['name' => 'Product listing page', 'estimated_hours' => 12],
                                    ['name' => 'Product filtering & search', 'estimated_hours' => 10],
                                    ['name' => 'Product detail page', 'estimated_hours' => 10],
                                ]
                            ],
                        ]
                    ],
                    [
                        'name' => 'Backend Development',
                        'description' => 'API development, database en admin functionaliteit',
                        'estimated_hours' => 60,
                        'default_hourly_rate' => 90.00,
                        'days_from_start' => 10,
                        'duration_days' => 25,
                        'tasks' => [
                            [
                                'name' => 'Database Design',
                                'description' => 'Database schema en migrations',
                                'estimated_hours' => 12,
                                'subtasks' => [
                                    ['name' => 'Database schema design', 'estimated_hours' => 4],
                                    ['name' => 'Migrations development', 'estimated_hours' => 4],
                                    ['name' => 'Seeders & test data', 'estimated_hours' => 4],
                                ]
                            ],
                            [
                                'name' => 'API Development',
                                'description' => 'REST API voor alle frontend functionaliteit',
                                'estimated_hours' => 30,
                                'subtasks' => [
                                    ['name' => 'Products API', 'estimated_hours' => 10],
                                    ['name' => 'Orders API', 'estimated_hours' => 8],
                                    ['name' => 'User authentication API', 'estimated_hours' => 6],
                                ]
                            ],
                        ]
                    ],
                ]
            ],
            [
                'name' => 'Mobile App Development',
                'category' => 'app',
                'description' => 'Cross-platform mobile app met React Native',
                'status' => 'active',
                'milestones' => [
                    [
                        'name' => 'App Setup & Design',
                        'description' => 'Project setup en UI/UX design',
                        'estimated_hours' => 32,
                        'default_hourly_rate' => 100.00,
                        'days_from_start' => 0,
                        'duration_days' => 8,
                        'tasks' => [
                            [
                                'name' => 'React Native Setup',
                                'description' => 'Development environment en app structure',
                                'estimated_hours' => 8,
                                'subtasks' => [
                                    ['name' => 'React Native init', 'estimated_hours' => 2],
                                    ['name' => 'Navigation setup', 'estimated_hours' => 3],
                                    ['name' => 'State management setup', 'estimated_hours' => 3],
                                ]
                            ],
                            [
                                'name' => 'UI/UX Design',
                                'description' => 'Complete app design en component library',
                                'estimated_hours' => 24,
                                'subtasks' => [
                                    ['name' => 'Design system creation', 'estimated_hours' => 8],
                                    ['name' => 'Screen mockups', 'estimated_hours' => 12],
                                    ['name' => 'Interactive prototype', 'estimated_hours' => 4],
                                ]
                            ]
                        ]
                    ],
                    [
                        'name' => 'Core Features Development',
                        'description' => 'Main app features en functionaliteit',
                        'estimated_hours' => 60,
                        'default_hourly_rate' => 95.00,
                        'days_from_start' => 8,
                        'duration_days' => 25,
                        'tasks' => [
                            [
                                'name' => 'Authentication Module',
                                'description' => 'User login, registration en profile management',
                                'estimated_hours' => 20,
                                'subtasks' => [
                                    ['name' => 'Login screen', 'estimated_hours' => 6],
                                    ['name' => 'Registration flow', 'estimated_hours' => 8],
                                    ['name' => 'Profile management', 'estimated_hours' => 6],
                                ]
                            ],
                            [
                                'name' => 'Main App Features',
                                'description' => 'Core business logic en user features',
                                'estimated_hours' => 40,
                                'subtasks' => [
                                    ['name' => 'Dashboard development', 'estimated_hours' => 15],
                                    ['name' => 'Data synchronization', 'estimated_hours' => 12],
                                    ['name' => 'Offline functionality', 'estimated_hours' => 8],
                                ]
                            ]
                        ]
                    ],
                ]
            ],
            [
                'name' => 'Marketing Website',
                'category' => 'marketing',
                'description' => 'Marketing website met blog, lead generation en analytics',
                'status' => 'active',
                'milestones' => [
                    [
                        'name' => 'Website Development',
                        'description' => 'Marketing website met moderne design',
                        'estimated_hours' => 40,
                        'default_hourly_rate' => 75.00,
                        'days_from_start' => 0,
                        'duration_days' => 15,
                        'tasks' => [
                            [
                                'name' => 'Homepage & Landing Pages',
                                'description' => 'Homepage en conversion-focused landing pages',
                                'estimated_hours' => 24,
                                'subtasks' => [
                                    ['name' => 'Homepage design & development', 'estimated_hours' => 12],
                                    ['name' => 'Service landing pages', 'estimated_hours' => 8],
                                    ['name' => 'Contact & about pages', 'estimated_hours' => 4],
                                ]
                            ],
                            [
                                'name' => 'Blog & Content Management',
                                'description' => 'Blog systeem en content management',
                                'estimated_hours' => 16,
                                'subtasks' => [
                                    ['name' => 'Blog listing & detail pages', 'estimated_hours' => 8],
                                    ['name' => 'Content management system', 'estimated_hours' => 6],
                                    ['name' => 'SEO optimization', 'estimated_hours' => 2],
                                ]
                            ]
                        ]
                    ],
                ]
            ]
        ];

        foreach ($templates as $templateData) {
            $milestones = $templateData['milestones'];
            unset($templateData['milestones']);

            // Only add fields that exist in the database
            $cleanTemplateData = [];
            $availableColumns = Schema::getColumnListing('project_templates');
            
            foreach ($templateData as $key => $value) {
                if (in_array($key, $availableColumns)) {
                    $cleanTemplateData[$key] = $value;
                }
            }

            // Add company and user data if columns exist
            if ($company && in_array('company_id', $availableColumns)) {
                $cleanTemplateData['company_id'] = $company->id;
            }
            if ($user && in_array('created_by', $availableColumns)) {
                $cleanTemplateData['created_by'] = $user->id;
            }

            $template = ProjectTemplate::create($cleanTemplateData);

            foreach ($milestones as $milestoneIndex => $milestoneData) {
                $tasks = $milestoneData['tasks'] ?? [];
                unset($milestoneData['tasks']);
                
                $milestoneData['project_template_id'] = $template->id;
                $milestoneData['sort_order'] = $milestoneIndex + 1;

                $milestone = TemplateMilestone::create($milestoneData);

                foreach ($tasks as $taskIndex => $taskData) {
                    $subtasks = $taskData['subtasks'] ?? [];
                    unset($taskData['subtasks']);
                    
                    $taskData['template_milestone_id'] = $milestone->id;
                    $taskData['sort_order'] = $taskIndex + 1;

                    $task = TemplateTask::create($taskData);

                    foreach ($subtasks as $subtaskIndex => $subtaskData) {
                        $subtaskData['template_task_id'] = $task->id;
                        $subtaskData['sort_order'] = $subtaskIndex + 1;

                        TemplateSubtask::create($subtaskData);
                    }
                }
            }

            // Update template totals if method exists
            if (method_exists($template, 'updateTotals')) {
                $template->updateTotals();
            }
        }

        $this->command->info('Created ' . count($templates) . ' project templates with milestones, tasks, and subtasks.');
    }
}