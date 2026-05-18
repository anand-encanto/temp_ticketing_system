<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

class TestEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:email {--to= : Email address to send the test email to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email sending using specific SMTP credentials';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $to = $this->option('to') ?: 'encantodeveloper@gmail.com';

        // Set the mail configuration dynamically for this test
        Config::set('mail.mailers.smtp.transport', 'smtp');
        Config::set('mail.mailers.smtp.host', 'smtp.gmail.com');
        Config::set('mail.mailers.smtp.port', 587);
        Config::set('mail.mailers.smtp.encryption', 'tls');
        Config::set('mail.mailers.smtp.username', 'encantodeveloper@gmail.com');
        Config::set('mail.mailers.smtp.password', 'zsrylkrtypkokyik');
        Config::set('mail.from.address', 'encantodeveloper@gmail.com');
        Config::set('mail.from.name', "McDonald's Support");

        $this->info("Attempting to send a test email to: {$to}");
        $this->info("Using SMTP Host: smtp.gmail.com, Port: 587, User: encantodeveloper@gmail.com");

        try {
            Mail::raw("This is a test email to verify SMTP credentials.\n\n" . 
                      "Host: smtp.gmail.com\n" . 
                      "Port: 587\n" . 
                      "Username: encantodeveloper@gmail.com", function ($message) use ($to) {
                $message->to($to)
                        ->subject('SMTP Credentials Verification - Ticketing System');
            });
            $this->info('Email sent successfully! Please check your inbox (and spam/junk folder).');
        } catch (\Exception $e) {
            $this->error('Failed to send email. There seems to be an issue with the SMTP credentials or network connection.');
            $this->error('Error Message: ' . $e->getMessage());
        }
    }
}
