<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\TeamleaderImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Mail\ImportCompletedMail;

class ImportTeamleaderCustomer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 uur timeout voor grote imports
    public $tries = 1; // Geen retry - import is te complex

    protected $companyIds;
    protected $options;
    protected $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(array $companyIds, array $options, int $userId)
    {
        $this->companyIds = $companyIds;
        $this->options = $options;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('ImportTeamleaderCustomer job started', [
            'user_id' => $this->userId,
            'company_count' => count($this->companyIds),
            'options' => $this->options
        ]);

        $startTime = now();
        $user = User::find($this->userId);

        if (!$user) {
            Log::error('User not found for import job', ['user_id' => $this->userId]);
            return;
        }

        try {
            // Set authenticated user context for service methods that use Auth::user()
            Auth::setUser($user);

            // Gebruik de bestaande TeamleaderImportService
            $importService = new TeamleaderImportService();

            $result = $importService->importSelectedCompanies($this->companyIds, $this->options);

            $duration = now()->diffInSeconds($startTime);

            // Map result keys to expected format
            $resultFormatted = [
                'customers_imported' => $result['imported'] ?? 0,
                'customers_skipped' => $result['skipped'] ?? 0,
                'contacts_imported' => $result['contacts_imported'] ?? 0,
                'projects_imported' => $result['projects_imported'] ?? 0,
                'time_entries_imported' => $result['time_entries_imported'] ?? 0,
            ];

            Log::info('ImportTeamleaderCustomer job completed successfully', [
                'user_id' => $this->userId,
                'duration_seconds' => $duration,
                'customers_imported' => $resultFormatted['customers_imported'],
                'customers_skipped' => $resultFormatted['customers_skipped'],
                'contacts_imported' => $resultFormatted['contacts_imported'],
                'projects_imported' => $resultFormatted['projects_imported'],
                'time_entries_imported' => $resultFormatted['time_entries_imported']
            ]);

            // Stuur success email naar gebruiker
            Mail::to($user->email)->send(new ImportCompletedMail($user, $resultFormatted, $duration, true));

        } catch (\Exception $e) {
            $duration = now()->diffInSeconds($startTime);

            Log::error('ImportTeamleaderCustomer job failed', [
                'user_id' => $this->userId,
                'duration_seconds' => $duration,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Stuur failure email naar gebruiker
            Mail::to($user->email)->send(new ImportCompletedMail($user, ['error' => $e->getMessage()], $duration, false));

            // Re-throw om job als failed te markeren
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ImportTeamleaderCustomer job failed permanently', [
            'user_id' => $this->userId,
            'company_count' => count($this->companyIds),
            'error' => $exception->getMessage()
        ]);
    }
}
