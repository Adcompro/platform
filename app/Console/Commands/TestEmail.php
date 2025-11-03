<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestMail;

class TestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test email to verify mail configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        try {
            // Gebruik de nieuwe TestMail mailable met betere headers
            Mail::to($email)->send(new TestMail(
                'This is a professional test email from AdCompro Progress. Your email configuration is working correctly!'
            ));
            
            $this->info("✅ Professional HTML test email sent successfully to {$email}!");
            $this->info("Check your inbox (and spam folder if needed)");
            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Failed to send email: " . $e->getMessage());
            return 1;
        }
    }
}