<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class TestUserVerification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:test-verification {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test user email verification notification';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User with email {$email} not found.");
            return 1;
        }
        
        if ($user->hasVerifiedEmail()) {
            $this->info("User {$user->name} ({$email}) is already verified.");
            $this->info("Verified at: " . $user->email_verified_at);
            return 0;
        }
        
        $this->info("Sending verification email to {$user->name} ({$email})...");
        
        try {
            $user->sendEmailVerificationNotification();
            $this->info("✅ Verification email sent successfully!");
            $this->info("Check the email inbox for: {$email}");
            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Failed to send verification email!");
            $this->error("Error: " . $e->getMessage());
            return 1;
        }
    }
}