<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\ProjectTask;
use App\Models\ProjectSubtask;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();
        
        try {
            // Maak company aan (of gebruik bestaande)
            $company = Company::firstOrCreate(
                ['id' => 1],
                [
                    'name' => 'AdCompro BV',
                    'vat_number' => 'NL123456789B01',
                    'address' => 'Hoofdstraat 1, 1234 AB Amsterdam',
                    'email' => 'info@adcompro.nl',
                    'phone' => '+31 20 123 4567',
                    'website' => 'https://www.adcompro.nl',
                    'default_hourly_rate' => 85.00,
                    'is_main_invoicing' => true,
                    'is_active' => true,
                ]
            );

            // Maak gebruiker Marcel Altena aan
            $user = User::updateOrCreate(
                ['email' => 'marcel.altena@adcompro.nl'],
                [
                    'name' => 'Marcel Altena',
                    'password' => Hash::make('Examen%1'),
                    'company_id' => $company->id,
                    'role' => 'super_admin',
                    'email_verified_at' => now(),
                ]
            );

            $this->command->info('✅ User created: marcel.altena@adcompro.nl with password: Examen%1');

            // Maak een tweede company aan voor inter-company facturatie demo
            $company2 = Company::firstOrCreate(
                ['name' => 'TechSupport BV'],
                [
                    'vat_number' => 'NL987654321B01',
                    'address' => 'Techstraat 10, 5678 CD Eindhoven',
                    'email' => 'info@techsupport.nl',
                    'phone' => '+31 40 987 6543',
                    'website' => 'https://www.techsupport.nl',
                    'default_hourly_rate' => 75.00,
                    'is_main_invoicing' => false,
                    'is_active' => true,
                ]
            );

            // Maak customers aan
            $customer1 = Customer::firstOrCreate(
                ['email' => 'contact@clienta.nl'],
                [
                    'company_id' => $company->id,
                    'name' => 'Client A - Webdevelopment',
                    'phone' => '+31 20 555 1234',
                    'address' => 'Clientstraat 100, 1000 AA Amsterdam',
                    'contact_person' => 'Jan Jansen',
                    'company' => 'Client A BV',
                    'notes' => 'Premium klant sinds 2020. Voornamelijk webprojecten.',
                    'status' => 'active',
                    'is_active' => true,
                ]
            );

            $customer2 = Customer::firstOrCreate(
                ['email' => 'info@startupx.nl'],
                [
                    'company_id' => $company->id,
                    'name' => 'StartupX - Mobile Apps',
                    'email' => 'info@startupx.nl',
                    'phone' => '+31 30 555 5678',
                    'address' => 'Innovatielaan 50, 3500 AB Utrecht',
                    'contact_person' => 'Lisa de Vries',
                    'company' => 'StartupX BV',
                    'notes' => 'Focus op mobile app development en API integraties.',
                    'status' => 'active',
                    'is_active' => true,
                ]
            );

            // Maak projects aan
            $project1 = Project::firstOrCreate(
                ['name' => 'E-commerce Platform Redesign'],
                [
                    'company_id' => $company->id,
                    'customer_id' => $customer1->id,
                    'description' => 'Complete redesign van het e-commerce platform inclusief nieuwe checkout flow, product catalogus en admin dashboard.',
                    'status' => 'active',
                    'start_date' => now()->subMonths(2),
                    'end_date' => now()->addMonths(4),
                    'monthly_fee' => 5000.00,
                    'fee_start_date' => now()->subMonths(2),
                    'fee_rollover_enabled' => true,
                    'default_hourly_rate' => 95.00,
                    'main_invoicing_company_id' => $company->id,
                    'vat_rate' => 21.00,
                    'notes' => 'High priority project. Weekly status meetings op vrijdag.',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ]
            );

            // Voeg team members toe aan project
            $project1->users()->syncWithoutDetaching([
                $user->id => [
                    'role_override' => 'project_manager',
                    'can_edit_fee' => true,
                    'can_view_financials' => true,
                    'can_log_time' => true,
                    'can_approve_time' => true,
                    'added_by' => $user->id,
                    'added_at' => now(),
                ]
            ]);

            // Skip companies voor nu (role column issue)

            // Maak milestones aan voor project 1
            $milestone1 = ProjectMilestone::create([
                'project_id' => $project1->id,
                'name' => 'Phase 1: Design & Prototyping',
                'description' => 'UI/UX design, wireframes en interactieve prototypes voor alle hoofdfunctionaliteiten.',
                'status' => 'completed',
                'start_date' => now()->subMonths(2),
                'end_date' => now()->subMonth(),
                'sort_order' => 1,
                'fee_type' => 'in_fee',
                'pricing_type' => 'fixed_price',
                'fixed_price' => 8500.00,
                'estimated_hours' => 80,
                'invoicing_trigger' => 'completion',
                'deliverables' => 'Figma designs, Style guide, Interactive prototype, User flow diagrams',
                'source_type' => 'manual',
            ]);

            $milestone2 = ProjectMilestone::create([
                'project_id' => $project1->id,
                'name' => 'Phase 2: Frontend Development',
                'description' => 'React implementatie van alle UI componenten en paginas.',
                'status' => 'in_progress',
                'start_date' => now()->subMonth(),
                'end_date' => now()->addMonth(),
                'sort_order' => 2,
                'fee_type' => 'in_fee',
                'pricing_type' => 'hourly_rate',
                'hourly_rate_override' => 95.00,
                'estimated_hours' => 120,
                'invoicing_trigger' => 'approval',
                'deliverables' => 'React components, Responsive layouts, Unit tests',
                'source_type' => 'manual',
            ]);

            $milestone3 = ProjectMilestone::create([
                'project_id' => $project1->id,
                'name' => 'Phase 3: Backend & Integration',
                'description' => 'API development, database setup en third-party integraties.',
                'status' => 'pending',
                'start_date' => now()->addMonth(),
                'end_date' => now()->addMonths(3),
                'sort_order' => 3,
                'fee_type' => 'in_fee',
                'pricing_type' => 'hourly_rate',
                'estimated_hours' => 160,
                'invoicing_trigger' => 'delivery',
                'source_type' => 'manual',
            ]);

            // Maak tasks aan voor milestone 2 (in progress)
            $task1 = ProjectTask::create([
                'project_milestone_id' => $milestone2->id,
                'name' => 'Homepage Components',
                'description' => 'Hero section, product carousel, testimonials en footer componenten.',
                'status' => 'completed',
                'start_date' => now()->subMonth(),
                'end_date' => now()->subWeeks(2),
                'sort_order' => 1,
                'fee_type' => 'in_fee',
                'pricing_type' => 'hourly_rate',
                'estimated_hours' => 24,
                'source_type' => 'manual',
            ]);

            $task2 = ProjectTask::create([
                'project_milestone_id' => $milestone2->id,
                'name' => 'Product Catalog Pages',
                'description' => 'Product listing, filtering, sorting en detail pages.',
                'status' => 'in_progress',
                'start_date' => now()->subWeeks(2),
                'end_date' => now()->addWeek(),
                'sort_order' => 2,
                'fee_type' => 'in_fee',
                'pricing_type' => 'hourly_rate',
                'estimated_hours' => 40,
                'source_type' => 'manual',
            ]);

            $task3 = ProjectTask::create([
                'project_milestone_id' => $milestone2->id,
                'name' => 'Shopping Cart & Checkout',
                'description' => 'Winkelwagen functionaliteit en multi-step checkout proces.',
                'status' => 'pending',
                'start_date' => now()->addWeek(),
                'end_date' => now()->addWeeks(3),
                'sort_order' => 3,
                'fee_type' => 'in_fee',
                'pricing_type' => 'hourly_rate',
                'estimated_hours' => 32,
                'source_type' => 'manual',
            ]);

            // Maak subtasks aan voor task 2 (in progress)
            ProjectSubtask::create([
                'project_task_id' => $task2->id,
                'name' => 'Product Grid Component',
                'description' => 'Responsive grid layout met lazy loading.',
                'status' => 'completed',
                'sort_order' => 1,
                'fee_type' => 'in_fee',
                'pricing_type' => 'hourly_rate',
                'estimated_hours' => 8,
                'source_type' => 'manual',
            ]);

            ProjectSubtask::create([
                'project_task_id' => $task2->id,
                'name' => 'Filter Sidebar',
                'description' => 'Categorie, prijs en eigenschappen filters.',
                'status' => 'in_progress',
                'sort_order' => 2,
                'fee_type' => 'in_fee',
                'pricing_type' => 'hourly_rate',
                'estimated_hours' => 12,
                'source_type' => 'manual',
            ]);

            ProjectSubtask::create([
                'project_task_id' => $task2->id,
                'name' => 'Product Quick View Modal',
                'description' => 'Modal voor snel bekijken van product details.',
                'status' => 'pending',
                'sort_order' => 3,
                'fee_type' => 'in_fee',
                'pricing_type' => 'hourly_rate',
                'estimated_hours' => 6,
                'source_type' => 'manual',
            ]);

            ProjectSubtask::create([
                'project_task_id' => $task2->id,
                'name' => 'Sorting Functionality',
                'description' => 'Sorteren op prijs, naam, populariteit.',
                'status' => 'pending',
                'sort_order' => 4,
                'fee_type' => 'in_fee',
                'pricing_type' => 'hourly_rate',
                'estimated_hours' => 4,
                'source_type' => 'manual',
            ]);

            // Tweede project
            $project2 = Project::firstOrCreate(
                ['name' => 'Mobile App Development - HealthTracker'],
                [
                    'company_id' => $company->id,
                    'customer_id' => $customer2->id,
                    'description' => 'Native iOS en Android app voor health tracking met wearable integratie.',
                    'status' => 'active',
                    'start_date' => now()->subMonth(),
                    'end_date' => now()->addMonths(5),
                    'monthly_fee' => 8000.00,
                    'fee_start_date' => now()->subMonth(),
                    'fee_rollover_enabled' => false,
                    'default_hourly_rate' => 110.00,
                    'main_invoicing_company_id' => $company->id,
                    'vat_rate' => 21.00,
                    'notes' => 'Agile development met 2-weekly sprints.',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ]
            );

            // Milestone voor project 2
            ProjectMilestone::create([
                'project_id' => $project2->id,
                'name' => 'Sprint 1: Core Features',
                'description' => 'User registration, dashboard en basic tracking features.',
                'status' => 'in_progress',
                'start_date' => now()->subMonth(),
                'end_date' => now()->addWeeks(2),
                'sort_order' => 1,
                'fee_type' => 'in_fee',
                'pricing_type' => 'fixed_price',
                'fixed_price' => 15000.00,
                'estimated_hours' => 140,
                'invoicing_trigger' => 'approval',
                'source_type' => 'manual',
            ]);

            DB::commit();
            
            $this->command->info('✅ Sample data created successfully!');
            $this->command->info('');
            $this->command->info('📋 Created:');
            $this->command->info('- 2 Companies (AdCompro BV, TechSupport BV)');
            $this->command->info('- 2 Customers (Client A, StartupX)');
            $this->command->info('- 2 Projects with milestones, tasks and subtasks');
            $this->command->info('');
            $this->command->info('👤 Login credentials:');
            $this->command->info('Email: marcel.altena@adcompro.nl');
            $this->command->info('Password: Examen%1');
            $this->command->info('Role: Super Admin');
            
        } catch (\Exception $e) {
            DB::rollback();
            $this->command->error('Error creating sample data: ' . $e->getMessage());
            throw $e;
        }
    }
}