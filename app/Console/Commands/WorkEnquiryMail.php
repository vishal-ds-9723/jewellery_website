<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class WorkEnquiryMail extends Command
{
    protected $signature = 'app:work-enquiry-mail {--once : Process a single job} {--stop-when-empty : Exit when the queue is empty}';

    protected $description = 'Deliver queued enquiry confirmations after checking the SMTP sender configuration';

    public function handle(): int
    {
        $username = config('mail.mailers.smtp.username');
        $sender = config('mail.from.address');
        if (! filter_var($username, FILTER_VALIDATE_EMAIL)
            || ! filter_var($sender, FILTER_VALIDATE_EMAIL)
            || $sender === 'hello@example.com'
            || ! config('mail.mailers.smtp.password')) {
            $this->error('Email delivery is not configured. Set MAIL_USERNAME, MAIL_FROM_ADDRESS and MAIL_PASSWORD in .env, refresh the config cache, then run this command again. Pending emails were not processed.');

            return self::FAILURE;
        }

        return $this->call('queue:work', [
            'connection' => 'database',
            '--queue' => 'enquiry-confirmations',
            '--sleep' => 3,
            '--tries' => 3,
            '--timeout' => 60,
            '--once' => $this->option('once'),
            '--stop-when-empty' => $this->option('stop-when-empty'),
        ]);
    }
}
