<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class VerifyUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:verify {email} {--show-link : Show the verification link}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manually verify a user or show their verification link';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $showLink = $this->option('show-link');
        
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
        
        if ($showLink) {
            // Generate and show verification link
            $verificationUrl = \URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(60),
                [
                    'id' => $user->getKey(),
                    'hash' => sha1($user->getEmailForVerification()),
                ]
            );
            
            $this->info("Verification link for {$user->name} ({$email}):");
            $this->line("");
            $this->line($verificationUrl);
            $this->line("");
            $this->warn("This link is valid for 60 minutes.");
        } else {
            // Manually verify the user
            $user->email_verified_at = now();
            $user->save();
            
            $this->info("✅ User {$user->name} ({$email}) has been manually verified!");
            $this->info("Verified at: " . $user->email_verified_at);
        }
        
        return 0;
    }
}