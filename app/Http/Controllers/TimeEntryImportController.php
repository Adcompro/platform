<?php

namespace App\Http\Controllers;

use App\Models\TimeEntry;
use App\Models\User;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\ProjectTask;
use App\Models\ProjectSubtask;
use App\Models\Customer;
use App\Models\TeamleaderProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TimeEntryImportController extends Controller
{
    /**
     * Show upload form
     */
    public function index()
    {
        // Authorization check - alleen admin/super_admin
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can import time entries.');
        }

        // Haal statistics op voor info
        $stats = [
            'total_entries' => TimeEntry::count(),
            'users_count' => User::where('is_active', true)->count(),
            'projects_count' => Project::whereIn('status', ['active', 'on_hold'])->count(),
        ];

        return view('time-entries.import.upload', compact('stats'));
    }

    /**
     * Upload bestand en parse data
     */
    public function upload(Request $request)
    {
        // Authorization
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403);
        }

        // Valideer upload
        $validated = $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // Max 10MB
        ]);

        try {
            $file = $request->file('file');

            // Parse Excel/CSV bestand
            $spreadsheet = IOFactory::load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            if (empty($rows)) {
                return back()->with('error', 'File is empty or could not be read.');
            }

            // Eerste rij is header
            $headers = array_shift($rows);

            // Detect kolom positie
            $mapping = $this->detectColumnMapping($headers);

            if (!$mapping['valid']) {
                return back()->with('error', 'Could not detect required columns. Make sure your file has: Date, User, Hours, Description, Project');
            }

            // Parse alle rijen
            $parsed = [];
            $errors = [];
            $rowNumber = 2; // Start bij 2 (1 is header)

            foreach ($rows as $row) {
                // Skip lege rijen
                if (empty(array_filter($row))) {
                    $rowNumber++;
                    continue;
                }

                // Parse rij
                $entry = $this->parseRow($row, $mapping, $rowNumber);

                if ($entry['errors']) {
                    $errors[] = [
                        'row' => $rowNumber,
                        'data' => $row,
                        'errors' => $entry['errors']
                    ];
                } else {
                    $parsed[] = $entry['data'];
                }

                $rowNumber++;
            }

            // Bewaar parsed data in session voor preview
            session([
                'import_data' => $parsed,
                'import_errors' => $errors,
                'import_filename' => $file->getClientOriginalName(),
            ]);

            return redirect()->route('time-entries.import.preview');

        } catch (\Exception $e) {
            Log::error('Time entry import upload error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Error reading file: ' . $e->getMessage());
        }
    }

    /**
     * Preview geïmporteerde data
     */
    public function preview()
    {
        // Authorization
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403);
        }

        $data = session('import_data', []);
        $errors = session('import_errors', []);
        $filename = session('import_filename', 'unknown');

        if (empty($data) && empty($errors)) {
            return redirect()->route('time-entries.import.index')
                ->with('error', 'No import data found. Please upload a file first.');
        }

        return view('time-entries.import.preview', compact('data', 'errors', 'filename'));
    }

    /**
     * Execute import
     */
    public function import(Request $request)
    {
        // Authorization
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403);
        }

        $data = session('import_data', []);

        if (empty($data)) {
            return redirect()->route('time-entries.import.index')
                ->with('error', 'No import data found. Please upload a file first.');
        }

        set_time_limit(600); // 10 minuten voor grote imports
        ini_set('max_execution_time', 600);

        try {
            DB::beginTransaction();

            $imported = 0;
            $skipped = 0;
            $errors = [];

            // VERBETERDE DUPLICATE DETECTION (31-10-2025 - FIX: Decimal normalization)
            // Maak snapshot van bestaande entries VOOR de import start
            // Filter op company voor performance
            $existingEntriesSnapshot = TimeEntry::select('id', 'user_id', 'project_id', 'entry_date', 'hours', 'minutes', 'description')
                ->where('company_id', Auth::user()->company_id)
                ->get()
                ->map(function($entry) {
                    // KRITIEK: Normaliseer hours/minutes als INTEGERS voor consistente hash
                    // Database: 0.50 hours → moet matchen met parsed: 0.5 hours
                    // Oplossing: Convert both naar total minutes (integer)
                    $totalMinutes = round(floatval($entry->hours) * 60 + floatval($entry->minutes));

                    // KRITIEK: Normaliseer datum naar Y-m-d format (zonder tijd!)
                    // Database kan datetime zijn: "2025-08-21 00:00:00"
                    // Parsed entry is altijd: "2025-08-21"
                    $normalizedDate = \Carbon\Carbon::parse($entry->entry_date)->format('Y-m-d');

                    // Normaliseer description voor betere matching
                    $normalizedDesc = trim(strtolower($entry->description ?? ''));

                    // Maak unieke hash met genormaliseerde data
                    $hash = md5(
                        $entry->user_id . '|' .
                        $entry->project_id . '|' .
                        $normalizedDate . '|' .  // GEBRUIK GENORMALISEERDE DATUM
                        $totalMinutes . '|' .  // GEBRUIK TOTAL MINUTES ipv hours+minutes
                        $normalizedDesc
                    );

                    return [
                        'id' => $entry->id,
                        'hash' => $hash,
                        'user_id' => $entry->user_id,
                        'project_id' => $entry->project_id,
                        'entry_date' => $entry->entry_date,
                        'total_minutes' => $totalMinutes,
                        'description' => $entry->description
                    ];
                })
                ->keyBy('hash')
                ->toArray();

            Log::info('Time entry import started', [
                'company_id' => Auth::user()->company_id,
                'existing_entries' => count($existingEntriesSnapshot),
                'entries_to_import' => count($data),
                'imported_by' => Auth::id()
            ]);

            foreach ($data as $entry) {
                try {
                    // KRITIEK: Normaliseer hours/minutes naar total minutes (zelfde als snapshot)
                    $totalMinutes = round(floatval($entry['hours']) * 60 + floatval($entry['minutes']));

                    // Normaliseer description voor consistente matching
                    $normalizedDesc = trim(strtolower($entry['description'] ?? ''));

                    // Check duplicate ALLEEN tegen snapshot van VOOR de import
                    $entryHash = md5(
                        $entry['user_id'] . '|' .
                        $entry['project_id'] . '|' .
                        $entry['entry_date'] . '|' .
                        $totalMinutes . '|' .  // GEBRUIK TOTAL MINUTES
                        $normalizedDesc
                    );

                    if (isset($existingEntriesSnapshot[$entryHash])) {
                        $skipped++;
                        $existingEntry = $existingEntriesSnapshot[$entryHash];

                        Log::info('Skipped duplicate time entry', [
                            'existing_id' => $existingEntry['id'],
                            'user_id' => $entry['user_id'],
                            'project_id' => $entry['project_id'],
                            'entry_date' => $entry['entry_date'],
                            'total_minutes' => $totalMinutes,
                            'description' => substr($entry['description'] ?? '', 0, 50),
                            'hash' => substr($entryHash, 0, 12)
                        ]);
                        continue;
                    }

                    // Create time entry
                    TimeEntry::create([
                        'user_id' => $entry['user_id'],
                        'company_id' => Auth::user()->company_id,
                        'project_id' => $entry['project_id'],
                        'customer_id' => $entry['customer_id'] ?? null,  // NIEUW: Customer ID
                        'project_milestone_id' => $entry['milestone_id'] ?? null,
                        'project_task_id' => $entry['task_id'] ?? null,
                        'project_subtask_id' => $entry['subtask_id'] ?? null,
                        'entry_date' => $entry['entry_date'],
                        'hours' => $entry['hours'],
                        'minutes' => $entry['minutes'],
                        'description' => $entry['description'],
                        'is_billable' => $entry['is_billable'],
                        'hourly_rate_used' => $entry['hourly_rate_override'] ?? null,  // NIEUW: Bewaar geïmporteerde rate
                        'status' => 'approved', // Auto-approve imports
                        'approved_by' => Auth::id(),
                        'approved_at' => now(),
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                    ]);

                    $imported++;

                } catch (\Exception $e) {
                    $errors[] = [
                        'entry' => $entry,
                        'error' => $e->getMessage()
                    ];
                }
            }

            DB::commit();

            // Automatically backfill activity logs for imported time entries
            Log::info('Running automatic activity backfill after Excel import', [
                'imported_entries' => $imported,
                'triggered_by' => Auth::id()
            ]);

            try {
                Artisan::call('activities:backfill-time-entries-improved');
                Log::info('Activity backfill completed successfully after Excel import');
            } catch (\Exception $e) {
                Log::error('Activity backfill failed after Excel import', [
                    'error' => $e->getMessage()
                ]);
                // Don't fail the import if backfill fails, just log it
            }

            // Clear session data
            session()->forget(['import_data', 'import_errors', 'import_filename']);

            $message = "Successfully imported {$imported} time entries.";
            if ($skipped > 0) {
                $message .= " Skipped {$skipped} duplicates.";
            }
            if (count($errors) > 0) {
                $message .= " " . count($errors) . " errors occurred.";
            }

            return redirect()->route('time-entries.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Time entry import error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Detect kolom mapping van headers
     */
    protected function detectColumnMapping($headers)
    {
        $mapping = [
            'date' => null,
            'user' => null,
            'hours' => null,
            'duration_minutes' => null,  // NIEUW: Voor Teamleader exports (duur in minuten)
            'hourly_rate' => null,       // NIEUW: Uurprijs voor rate berekening
            'description' => null,
            'customer' => null,          // NIEUW: Customer kolom voor auto-create
            'project' => null,
            'project_code' => null,      // NIEUW: Project code voor matching
            'milestone' => null,
            'task' => null,
            'subtask' => null,
            'billable' => null,
        ];

        foreach ($headers as $index => $header) {
            $header = strtolower(trim($header));

            // Date kolom
            if (in_array($header, ['date', 'datum', 'entry_date', 'entry date'])) {
                $mapping['date'] = $index;
            }
            // User kolom
            elseif (in_array($header, ['user', 'gebruiker', 'name', 'naam', 'employee'])) {
                $mapping['user'] = $index;
            }
            // Hours kolom (decimaal: 2.5 uur)
            elseif (in_array($header, ['hours', 'uren', 'time', 'tijd'])) {
                $mapping['hours'] = $index;
            }
            // Duration in minutes kolom (NIEUW: voor Teamleader exports)
            elseif (in_array($header, ['duur (in minuten)', 'minuten', 'duration', 'duration (minutes)'])) {
                $mapping['duration_minutes'] = $index;
            }
            // Hourly rate kolom (NIEUW: uurprijs voor rate berekening)
            elseif (in_array($header, ['uurprijs', 'hourly rate', 'rate', 'tarief'])) {
                $mapping['hourly_rate'] = $index;
            }
            // Description kolom
            elseif (in_array($header, ['description', 'omschrijving', 'beschrijving', 'note', 'notes', 'opmerking'])) {
                $mapping['description'] = $index;
            }
            // Customer kolom (NIEUW: voor auto-create)
            elseif (in_array($header, ['customer', 'klant', 'client', 'bedrijf', 'company'])) {
                $mapping['customer'] = $index;
            }
            // Project kolom
            elseif (in_array($header, ['project', 'project name', 'projectnaam'])) {
                $mapping['project'] = $index;
            }
            // Project code kolom (NIEUW: voor matching)
            elseif (in_array($header, ['project code', 'projectcode', 'code'])) {
                $mapping['project_code'] = $index;
            }
            // Milestone kolom (NIEUW: "Fase" voor Teamleader)
            elseif (in_array($header, ['milestone', 'mijlpaal', 'fase', 'phase'])) {
                $mapping['milestone'] = $index;
            }
            // Task kolom (NIEUW: "Type" voor Teamleader)
            elseif (in_array($header, ['task', 'taak', 'type'])) {
                $mapping['task'] = $index;
            }
            // Subtask kolom
            elseif (in_array($header, ['subtask', 'subtaak', 'sub-task'])) {
                $mapping['subtask'] = $index;
            }
            // Billable kolom
            elseif (in_array($header, ['billable', 'factureerbaar', 'billable?'])) {
                $mapping['billable'] = $index;
            }
        }

        // Check verplichte velden - NU FLEXIBEL: hours OF duration_minutes
        $mapping['valid'] = !is_null($mapping['date']) &&
                           !is_null($mapping['user']) &&
                           (!is_null($mapping['hours']) || !is_null($mapping['duration_minutes'])) &&
                           !is_null($mapping['project']);

        return $mapping;
    }

    /**
     * Parse één rij uit Excel/CSV
     */
    protected function parseRow($row, $mapping, $rowNumber)
    {
        $errors = [];
        $data = [];

        // Parse date
        $dateValue = $row[$mapping['date']] ?? null;
        try {
            $data['entry_date'] = Carbon::parse($dateValue)->format('Y-m-d');
        } catch (\Exception $e) {
            $errors[] = "Invalid date format: {$dateValue}";
        }

        // Parse user (zoek op naam of email, maak aan indien niet bestaat)
        $userName = $row[$mapping['user']] ?? null;
        $user = User::where('name', 'like', "%{$userName}%")
            ->orWhere('email', 'like', "%{$userName}%")
            ->first();

        if (!$user) {
            // NIEUW: User bestaat niet, maak automatisch aan
            try {
                // Genereer een veilige email (lowercase, geen spaties)
                $safeEmail = strtolower(str_replace(' ', '.', $userName)) . '@temp-import.local';

                // Check of email al bestaat (dubbele invoer in Excel)
                $existingEmail = User::where('email', $safeEmail)->first();
                if ($existingEmail) {
                    $safeEmail = strtolower(str_replace(' ', '.', $userName)) . '.' . uniqid() . '@temp-import.local';
                }

                $user = User::create([
                    'name' => $userName,
                    'email' => $safeEmail,
                    'password' => bcrypt(\Illuminate\Support\Str::random(32)), // Random wachtwoord
                    'role' => 'user', // Default role
                    'is_active' => true,
                    'company_id' => Auth::user()->company_id, // Company van importeur
                    'email_verified_at' => now(), // Auto-verify
                ]);

                Log::info('Auto-created user during time entry import', [
                    'user_id' => $user->id,
                    'name' => $userName,
                    'email' => $safeEmail,
                    'imported_by' => Auth::id()
                ]);

            } catch (\Exception $e) {
                $errors[] = "Could not create user: {$userName} - " . $e->getMessage();
            }
        }

        if ($user) {
            $data['user_id'] = $user->id;
            $data['user_name'] = $user->name;
        }

        // Parse hours - FLEXIBEL: hours OF duration_minutes
        if (!is_null($mapping['duration_minutes']) && isset($row[$mapping['duration_minutes']])) {
            // NIEUW: Duur in minuten (voor Teamleader exports)
            $totalMinutes = intval($row[$mapping['duration_minutes']]);
            // BELANGRIJK: Sla ALLEEN minutes op, hours wordt berekend uit minutes
            // Dit voorkomt dubbeltelling in budget berekeningen (hours + minutes/60)
            $data['hours'] = round($totalMinutes / 60, 2);  // Voor database storage
            $data['minutes'] = 0;  // Zet minutes 0 om dubbeltelling te voorkomen
        } elseif (!is_null($mapping['hours']) && isset($row[$mapping['hours']])) {
            // BESTAAND: Decimale uren (2.5 uur = 2h 30m)
            $hoursValue = floatval(str_replace(',', '.', $row[$mapping['hours']]));
            $data['hours'] = floor($hoursValue);
            $data['minutes'] = round(($hoursValue - floor($hoursValue)) * 60);
        } else {
            $data['hours'] = 0;
            $data['minutes'] = 0;
        }

        // Parse description
        $data['description'] = $row[$mapping['description']] ?? '';

        // Parse hourly rate (optioneel - voor rate tracking/override)
        if (!is_null($mapping['hourly_rate']) && isset($row[$mapping['hourly_rate']])) {
            $hourlyRateValue = floatval(str_replace(',', '.', $row[$mapping['hourly_rate']]));
            $data['hourly_rate_override'] = $hourlyRateValue;
        }

        // Parse project EERST - zoek bestaande project
        $projectName = $row[$mapping['project']] ?? null;
        $projectCode = $row[$mapping['project_code']] ?? null;
        $project = null;
        $customer = null;

        if ($projectName) {
            // Zoek bestaande project - match op naam OF projectcode
            $query = Project::where('company_id', Auth::user()->company_id)
                ->where(function($q) use ($projectName, $projectCode) {
                    $q->where('name', $projectName);
                    if ($projectCode) {
                        $q->orWhere('project_code', $projectCode);
                    }
                });

            $project = $query->first();

            // Als project BESTAAT, gebruik customer van dat project (KRITIEKE FIX!)
            if ($project && $project->customer_id) {
                $customer = $project->customer;
                Log::info('Using existing project customer', [
                    'project_name' => $projectName,
                    'project_id' => $project->id,
                    'customer_name' => $customer->name,
                    'customer_id' => $customer->id
                ]);
            }
        }

        // Parse customer - alleen als NIET gevonden via bestaand project
        if (!$customer) {
            $customerName = !is_null($mapping['customer']) && isset($row[$mapping['customer']])
                ? $row[$mapping['customer']]
                : null;

            // ALLEEN als er een expliciete customer kolom is, probeer customer te matchen/aanmaken
            // NOOIT automatisch customers aanmaken o.b.v. project naam!
            if ($customerName) {
                // Zoek bestaande customer - VERBETERDE MATCHING LOGICA
                // Stap 1: Probeer exacte match op 'name' OF 'company' kolom
                // BELANGRIJK: Zoek ook in customers met NULL company_id (Teamleader import)
                $customer = Customer::where(function($q) {
                        $q->where('company_id', Auth::user()->company_id)
                          ->orWhereNull('company_id'); // KRITIEK: Ook NULL company_id (Teamleader imports)
                    })
                    ->where(function($q) use ($customerName) {
                        $q->where('name', $customerName)
                          ->orWhere('company', $customerName);
                    })
                    ->first();

                // Stap 2: Als geen exacte match, probeer LIKE match (partial matching)
                // Dit matcht bijv. "Anker" met "Anker Solix"
                if (!$customer) {
                    $customer = Customer::where(function($q) {
                            $q->where('company_id', Auth::user()->company_id)
                              ->orWhereNull('company_id'); // KRITIEK: Ook NULL company_id (Teamleader imports)
                        })
                        ->where(function($q) use ($customerName) {
                            $q->where('name', 'LIKE', "%{$customerName}%")
                              ->orWhere('company', 'LIKE', "%{$customerName}%");
                        })
                        ->first();

                    if ($customer) {
                        Log::info('Customer matched via partial match', [
                            'search_name' => $customerName,
                            'found_customer' => $customer->name,
                            'customer_id' => $customer->id
                        ]);
                    }
                }

                // Stap 3: Als nog steeds geen match, maak nieuwe customer aan
                if (!$customer) {
                    try {
                        $customer = Customer::create([
                            'company_id' => Auth::user()->company_id,
                            'name' => $customerName,
                            'company' => $customerName, // Vul ook company kolom
                            'status' => 'active',
                            'is_active' => true,
                            'created_by' => Auth::id(),
                        ]);

                        Log::info('Auto-created customer during time entry import', [
                            'customer_id' => $customer->id,
                            'name' => $customerName,
                            'imported_by' => Auth::id()
                        ]);
                    } catch (\Exception $e) {
                        Log::warning('Could not create customer', [
                            'name' => $customerName,
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                if ($customer) {
                    $data['customer_id'] = $customer->id;
                    $data['customer_name'] = $customer->name;
                }
            }
        } else {
            // Customer gevonden via bestaand project
            $data['customer_id'] = $customer->id;
            $data['customer_name'] = $customer->name;
        }

        // Vervolg met project logic (als project nog niet gevonden)
        if (!$project && $projectName) {
            try {
                    // NIEUW: Check eerst of project in Teamleader bestaat voor budget data
                    $teamleaderProject = TeamleaderProject::where('title', $projectName)
                        ->orWhere('title', 'LIKE', "%{$projectName}%")
                        ->first();

                    $monthlyFee = null;
                    $totalValue = null;
                    $teamleaderId = null;

                    if ($teamleaderProject) {
                        $monthlyFee = $teamleaderProject->budget_amount;
                        $totalValue = $teamleaderProject->budget_amount;
                        $teamleaderId = $teamleaderProject->teamleader_id;

                        Log::info('Found Teamleader project with budget data', [
                            'project_name' => $projectName,
                            'teamleader_title' => $teamleaderProject->title,
                            'budget_amount' => $teamleaderProject->budget_amount
                        ]);
                    }

                    $project = Project::create([
                        'company_id' => Auth::user()->company_id,
                        'customer_id' => $customer ? $customer->id : null,
                        'name' => $projectName,
                        'project_code' => $projectCode,
                        'teamleader_id' => $teamleaderId, // Link naar Teamleader
                        'status' => 'active',
                        'billing_frequency' => 'monthly',
                        'monthly_fee' => $monthlyFee, // Budget uit Teamleader!
                        'total_value' => $totalValue,  // Budget uit Teamleader!
                        'default_hourly_rate' => 165.00, // Default rate
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                    ]);

                    Log::info('Auto-created project during time entry import', [
                        'project_id' => $project->id,
                        'name' => $projectName,
                        'customer_id' => $customer ? $customer->id : null,
                        'monthly_fee' => $monthlyFee,
                        'from_teamleader' => $teamleaderProject ? true : false,
                        'imported_by' => Auth::id()
                    ]);
            } catch (\Exception $e) {
                $errors[] = "Could not create project: {$projectName} - " . $e->getMessage();
                Log::warning('Could not create project', [
                    'name' => $projectName,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Set project data als project gevonden/aangemaakt is
        if ($project) {
            $data['project_id'] = $project->id;
            $data['project_name'] = $project->name;

            // Store customer_id from project if not set yet
            if (!isset($data['customer_id']) && $project->customer_id) {
                $data['customer_id'] = $project->customer_id;
            }
        }

        // Als nog steeds geen project, geef error
        if (!$project) {
            $errors[] = "Project required but could not be found or created: {$projectName}";
        }

        // Parse milestone (auto-create als niet bestaat)
        if (!is_null($mapping['milestone']) && !empty($row[$mapping['milestone']])) {
            $milestoneName = trim($row[$mapping['milestone']]);

            // Zoek bestaande milestone
            $milestone = ProjectMilestone::where('name', $milestoneName)
                ->where('project_id', $data['project_id'] ?? null)
                ->first();

            // Als milestone niet bestaat, maak deze aan
            if (!$milestone && isset($data['project_id'])) {
                try {
                    // Extract sort order van naam (bijv. "1. Content" → 1, "10. Something" → 10)
                    $sortOrder = 999; // Default als geen nummer gevonden
                    if (preg_match('/^(\d+)\./', $milestoneName, $matches)) {
                        $sortOrder = intval($matches[1]);
                    }

                    $milestone = ProjectMilestone::create([
                        'project_id' => $data['project_id'],
                        'name' => $milestoneName,
                        'description' => 'Auto-created from time entry import',
                        'status' => 'pending',
                        'sort_order' => $sortOrder,
                        'fee_type' => 'in_fee',
                        'pricing_type' => 'hourly_rate',
                        'estimated_hours' => 0,
                        'source_type' => 'manual',
                    ]);

                    Log::info('Auto-created milestone during time entry import', [
                        'milestone_id' => $milestone->id,
                        'name' => $milestoneName,
                        'project_id' => $data['project_id'],
                        'sort_order' => $sortOrder,
                        'imported_by' => Auth::id()
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Could not create milestone', [
                        'name' => $milestoneName,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            if ($milestone) {
                $data['milestone_id'] = $milestone->id;
                $data['milestone_name'] = $milestone->name;
            }
        }

        // Parse task (auto-create als niet bestaat)
        if (!is_null($mapping['task']) && !empty($row[$mapping['task']]) && isset($data['milestone_id'])) {
            $taskName = trim($row[$mapping['task']]);

            // Zoek bestaande task binnen deze milestone
            $task = ProjectTask::where('name', $taskName)
                ->where('project_milestone_id', $data['milestone_id'])
                ->first();

            // Als task niet bestaat, maak deze aan
            if (!$task && isset($data['milestone_id'])) {
                try {
                    // Extract sort order van naam (bijv. "001 Press Releases" → 1, "023 Something" → 23)
                    $sortOrder = 999; // Default als geen nummer gevonden
                    if (preg_match('/^(\d+)\s+/', $taskName, $matches)) {
                        $sortOrder = intval($matches[1]);
                    }

                    $task = ProjectTask::create([
                        'project_milestone_id' => $data['milestone_id'],
                        'name' => $taskName,
                        'description' => 'Auto-created from time entry import',
                        'status' => 'pending',
                        'sort_order' => $sortOrder,
                        'fee_type' => 'in_fee',
                        'pricing_type' => 'hourly_rate',
                        'estimated_hours' => 0,
                        'source_type' => 'manual',
                    ]);

                    Log::info('Auto-created task during time entry import', [
                        'task_id' => $task->id,
                        'name' => $taskName,
                        'milestone_id' => $data['milestone_id'],
                        'sort_order' => $sortOrder,
                        'imported_by' => Auth::id()
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Could not create task', [
                        'name' => $taskName,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            if ($task) {
                $data['task_id'] = $task->id;
                $data['task_name'] = $task->name;
            }
        }

        // Parse subtask (optioneel)
        if (!is_null($mapping['subtask']) && !empty($row[$mapping['subtask']])) {
            $subtaskName = $row[$mapping['subtask']];
            $subtask = ProjectSubtask::where('name', 'like', "%{$subtaskName}%")
                ->whereHas('task.milestone', function($q) use ($data) {
                    if (isset($data['project_id'])) {
                        $q->where('project_id', $data['project_id']);
                    }
                })
                ->first();
            if ($subtask) {
                $data['subtask_id'] = $subtask->id;
                $data['subtask_name'] = $subtask->name;
            }
        }

        // Parse billable (optioneel, default = billable)
        $data['is_billable'] = 'billable'; // Default
        if (!is_null($mapping['billable']) && isset($row[$mapping['billable']])) {
            $billableValue = strtolower(trim($row[$mapping['billable']]));

            // UITGEBREID: Ondersteuning voor Nederlands EN Engels
            if (in_array($billableValue, ['no', 'nee', 'neen', 'false', '0', 'non-billable', 'niet factureerbaar'])) {
                $data['is_billable'] = 'non_billable';
            } elseif (in_array($billableValue, ['yes', 'ja', 'true', '1', 'billable', 'factureerbaar'])) {
                $data['is_billable'] = 'billable';
            }
            // Als geen match: behoud default (billable)
        }

        return [
            'data' => $data,
            'errors' => $errors
        ];
    }

    /**
     * Cancel import
     */
    public function cancel()
    {
        session()->forget(['import_data', 'import_errors', 'import_filename']);
        return redirect()->route('time-entries.import.index')
            ->with('success', 'Import cancelled.');
    }
}
