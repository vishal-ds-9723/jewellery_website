<?php

namespace Tests\Feature;

use App\Models\Enquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class SubmissionTest extends TestCase
{
    use RefreshDatabase;

    private function enquiry(array $changes = []): array
    {
        return array_replace([
            'submission_id' => (string) Str::uuid(),
            'name' => 'Test Customer',
            'email' => 'CUSTOMER@example.com',
            'phone' => '+91 98765 43210',
            'shopping_interest' => 'women',
            'appointment_date' => now('Asia/Kolkata')->addDay()->toDateString(),
            'appointment_slot' => 'morning',
            'message' => 'I would like to see bridal necklaces.',
            'website' => '',
        ], $changes);
    }

    public function test_public_pages_render_blade_views_and_forms_have_csrf_and_ajax_actions(): void
    {
        foreach (['/', '/about', '/collections', '/contact', '/product?item=noor'] as $url) {
            $this->get($url)->assertOk()->assertSee('R.A.')->assertDontSee('@extends');
        }
        $this->get('/contact')->assertSee('name="_token"', false)->assertSee('data-ajax-form', false)->assertSee(route('enquiries.store'), false);
        $this->get('/')->assertSee(route('newsletter.store'), false)->assertSee('name="email"', false);
        $this->get('/product.html?item=gul')->assertRedirect(route('product', ['item' => 'gul']));
    }

    public function test_valid_enquiry_is_saved_and_retry_is_idempotent(): void
    {
        $payload = $this->enquiry(['status' => 'confirmed', 'is_admin' => true]);
        $response = $this->postJson(route('enquiries.store'), $payload)
            ->assertCreated()->assertJsonStructure(['message', 'submission_id']);
        $this->assertNotSame($payload['submission_id'], $response->json('submission_id'));
        $this->postJson(route('enquiries.store'), $payload)->assertOk();
        $this->assertDatabaseCount('enquiries', 1);
        $this->assertDatabaseHas('enquiries', [
            'email' => 'customer@example.com', 'status' => 'new',
            'appointment_slot' => 'morning', 'phone' => '+91 98765 43210',
        ]);
    }

    public function test_invalid_fields_return_json_and_nothing_is_saved(): void
    {
        $this->postJson(route('enquiries.store'), $this->enquiry([
            'name' => '', 'email' => 'invalid', 'phone' => '-------',
            'appointment_date' => now('Asia/Kolkata')->subDay()->toDateString(),
            'appointment_slot' => 'midnight', 'shopping_interest' => 'unknown',
            'message' => str_repeat('x', 5001),
        ]))->assertUnprocessable()->assertJsonValidationErrors([
            'name', 'email', 'phone', 'appointment_date', 'appointment_slot', 'shopping_interest', 'message',
        ]);
        $this->assertDatabaseCount('enquiries', 0);
    }

    public function test_date_validation_uses_india_time_even_when_utc_is_previous_day(): void
    {
        $this->travelTo(Carbon::parse('2026-10-09 20:00:00', 'UTC'));
        $this->postJson(route('enquiries.store'), $this->enquiry(['appointment_date' => '2026-10-09']))
            ->assertUnprocessable()->assertJsonValidationErrors('appointment_date');
        $this->postJson(route('enquiries.store'), $this->enquiry(['appointment_date' => '2026-10-10']))
            ->assertCreated();
        $this->travelBack();
    }

    public function test_honeypot_is_rejected(): void
    {
        $this->postJson(route('enquiries.store'), $this->enquiry(['website' => 'spam.example']))
            ->assertUnprocessable();
        $this->postJson(route('newsletter.store'), ['email' => 'bot@example.com', 'website' => 'spam'])
            ->assertUnprocessable();
        $this->assertDatabaseCount('enquiries', 0);
        $this->assertDatabaseCount('newsletter_subscribers', 0);
    }

    public function test_newsletter_normalizes_and_deduplicates_email(): void
    {
        $this->postJson(route('newsletter.store'), ['email' => ' Reader@Example.com '])->assertOk()->assertJsonStructure(['message']);
        $this->postJson(route('newsletter.store'), ['email' => 'reader@example.com'])->assertOk();
        $this->assertDatabaseCount('newsletter_subscribers', 1);
        $this->assertDatabaseHas('newsletter_subscribers', ['email' => 'reader@example.com']);
    }

    public function test_unexpected_array_fields_return_validation_errors_instead_of_server_errors(): void
    {
        $this->postJson(route('enquiries.store'), $this->enquiry(['phone' => ['invalid']]))
            ->assertUnprocessable()->assertJsonValidationErrors('phone');
        $this->postJson(route('login.store'), ['email' => ['invalid'], 'password' => 'invalid'])
            ->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_newsletter_rejects_invalid_email(): void
    {
        $this->postJson(route('newsletter.store'), ['email' => 'invalid'])->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->assertDatabaseCount('newsletter_subscribers', 0);
    }

    public function test_enquiry_rate_limit_rejects_sixth_request(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson(route('enquiries.store'), $this->enquiry())->assertCreated();
        }
        $this->postJson(route('enquiries.store'), $this->enquiry())->assertTooManyRequests();
        $this->assertDatabaseCount('enquiries', 5);
    }

    public function test_newsletter_rate_limit_rejects_fourth_request(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->postJson(route('newsletter.store'), ['email' => "reader{$i}@example.com"])->assertOk();
        }
        $this->postJson(route('newsletter.store'), ['email' => 'extra@example.com'])->assertTooManyRequests();
    }

    public function test_database_failure_does_not_report_success(): void
    {
        Enquiry::creating(function () {
            throw new \RuntimeException('Simulated storage failure');
        });
        $this->postJson(route('enquiries.store'), $this->enquiry())->assertStatus(500)->assertDontSee('Your enquiry has been saved');
        $this->assertDatabaseCount('enquiries', 0);
    }

    public function test_form_can_also_submit_without_javascript(): void
    {
        $this->post(route('enquiries.store'), $this->enquiry())->assertRedirect(route('contact'))->assertSessionHas('success');
        $this->assertDatabaseCount('enquiries', 1);
    }
}
