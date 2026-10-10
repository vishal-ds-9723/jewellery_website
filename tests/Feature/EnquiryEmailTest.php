<?php

namespace Tests\Feature;

use App\Mail\EnquiryThankYou;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\SendQueuedMailable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class EnquiryEmailTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $changes = []): array
    {
        return array_replace([
            'submission_id' => (string) Str::uuid(),
            'name' => 'Aarav & Family',
            'email' => 'AARAV@example.com',
            'phone' => '+91 9876543210',
            'appointment_date' => now('Asia/Kolkata')->addDay()->toDateString(),
            'appointment_slot' => 'morning',
            'message' => 'Interested in your collection.',
        ], $changes);
    }

    public function test_new_enquiry_queues_email_for_submitter_once_even_after_retry(): void
    {
        $payload = $this->payload();
        $this->postJson('/enquiries', $payload)->assertCreated();
        $this->postJson('/enquiries', $payload)->assertOk();
        $this->assertDatabaseCount('enquiries', 1);
        $this->assertDatabaseCount('jobs', 1);
        $row = DB::table('jobs')->first();
        $this->assertSame('enquiry-confirmations', $row->queue);
        $job = unserialize(json_decode($row->payload, true)['data']['command']);
        $this->assertInstanceOf(SendQueuedMailable::class, $job);
        $this->assertInstanceOf(EnquiryThankYou::class, $job->mailable);
        $this->assertTrue($job->mailable->hasTo('aarav@example.com'));
        $this->assertSame('smtp', $job->mailable->mailer);
        $this->assertSame([], $job->mailable->cc);
        $this->assertSame([], $job->mailable->bcc);
    }

    public function test_validation_failure_does_not_queue_an_email(): void
    {
        $this->postJson('/enquiries', $this->payload(['email' => 'invalid']))
            ->assertUnprocessable();
        $this->assertDatabaseCount('enquiries', 0);
        $this->assertDatabaseCount('jobs', 0);
    }

    public function test_queue_storage_failure_rolls_back_so_a_retry_can_save_and_queue(): void
    {
        Mail::shouldReceive('mailer')->with('smtp')->andReturnSelf();
        Mail::shouldReceive('to')->with('aarav@example.com')->andReturnSelf();
        Mail::shouldReceive('queue')->once()->andThrow(new \RuntimeException('Simulated queue persistence failure'));
        $this->postJson('/enquiries', $this->payload())->assertStatus(500);
        $this->assertDatabaseCount('enquiries', 0);
        $this->assertDatabaseCount('jobs', 0);
    }

    public function test_queued_email_renders_logo_thank_you_and_plain_text_without_external_delivery(): void
    {
        config(['mail.mailers.smtp.transport' => 'array']);
        $this->postJson('/enquiries', $this->payload(['name' => '<script>Aarav</script>']))
            ->assertCreated();
        $transport = Mail::mailer('smtp')->getSymfonyTransport();
        $this->assertCount(0, $transport->messages(), 'HTTP submission must not send synchronously.');

        $job = Queue::connection('database')->pop('enquiry-confirmations');
        $job->fire();
        $this->assertDatabaseCount('jobs', 0);
        $this->assertCount(1, $transport->messages());
        $message = $transport->messages()->first()->getOriginalMessage();
        $this->assertSame('aarav@example.com', $message->getTo()[0]->getAddress());
        $this->assertStringContainsString('Thank you for your interest', $message->getSubject());
        $this->assertStringContainsString('R.A. PARIKH', $message->getHtmlBody());
        $this->assertStringContainsString('&lt;script&gt;Aarav&lt;/script&gt;', $message->getHtmlBody());
        $this->assertStringNotContainsString('<script>Aarav</script>', $message->getHtmlBody());
        $this->assertStringContainsString('get in touch with updates', $message->getTextBody());
        $mime = $message->toString();
        $this->assertStringContainsString('multipart/related', $mime);
        $this->assertStringNotContainsString('multipart/mixed', $mime);
        $this->assertStringNotContainsString('filename=', $mime);
        $this->assertCount(1, $message->getAttachments());
        $logo = $message->getAttachments()[0];
        $this->assertSame('inline', $logo->getDisposition());
        $this->assertNull($logo->getFilename());
        $this->assertSame('image/png', $logo->getContentType());
        $this->assertSame(file_get_contents(public_path('assets/ra-parikh-logo.png')), $logo->getBody());
        $this->assertSame(1, substr_count($message->getHtmlBody(), '<img '));
        $this->assertStringContainsString('src="cid:'.$logo->getContentId().'"', $message->getHtmlBody());
        $this->assertLessThan(
            strpos($message->getHtmlBody(), '>R.A. PARIKH</p>'),
            strpos($message->getHtmlBody(), '<img '),
            'The single logo must appear above the brand heading.',
        );
        $this->assertStringNotContainsString('127.0.0.1', $message->getHtmlBody());
    }

    public function test_worker_does_not_consume_pending_email_without_sender_configuration(): void
    {
        $this->postJson('/enquiries', $this->payload())->assertCreated();
        config(['mail.mailers.smtp.username' => null]);
        $this->artisan('app:work-enquiry-mail', ['--once' => true])->assertFailed();
        $this->assertDatabaseCount('jobs', 1);
        $this->assertSame(0, DB::table('jobs')->value('attempts'));
    }
}
