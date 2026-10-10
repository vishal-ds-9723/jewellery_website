<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EnquiryThankYou extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 60;

    public array $backoff = [60, 300];

    public function __construct(public string $customerName)
    {
        $this->onConnection('database')->onQueue('enquiry-confirmations');
        // The job and enquiry share one database transaction. Workers can only
        // see the job after commit; a rollback removes both records.
        $this->beforeCommit();
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Thank you for your interest | R.A. Parikh Jewellers');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.enquiry-thank-you',
            text: 'emails.enquiry-thank-you-text',
            with: [
                // Keep the logo inline without a downloadable filename. Some
                // email clients also list named inline images as attachments.
                'logo' => Attachment::fromData(
                    static fn () => file_get_contents(public_path('assets/ra-parikh-logo.png')),
                )->withMime('image/png'),
            ],
        );
    }
}
