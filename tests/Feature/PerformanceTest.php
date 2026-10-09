<?php

namespace Tests\Feature;

use App\Models\Enquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->forceFill(['is_admin' => true])->save();

        return $user;
    }

    private function enquiry(array $attributes = []): Enquiry
    {
        return Enquiry::create(array_replace([
            'submission_id' => (string) Str::uuid(), 'name' => 'Customer',
            'email' => 'customer@example.com', 'phone' => '+91 9876543210',
            'appointment_date' => now()->addDay()->toDateString(),
            'appointment_slot' => 'morning', 'message' => 'An enquiry',
        ], $attributes));
    }

    public function test_display_pages_are_cacheable_without_session_cookies_or_form_tokens(): void
    {
        foreach (['/about', '/collections', '/product?item=gul'] as $path) {
            $response = $this->get($path)->assertOk()
                ->assertHeader('Cache-Control', 'max-age=60, public')
                ->assertDontSee('name="csrf-token"', false)
                ->assertDontSee('name="_token"', false);
            $this->assertEmpty($response->headers->getCookies());
        }
    }

    public function test_form_pages_and_admin_are_never_publicly_cached(): void
    {
        foreach (['/', '/contact', '/admin/login'] as $path) {
            $response = $this->get($path)->assertOk()
                ->assertHeader('Cache-Control', 'no-store, private')
                ->assertSee('name="_token"', false);
            $this->assertNotEmpty($response->headers->getCookies());
        }
        $this->actingAs($this->admin())->get('/admin')->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_cursor_pagination_has_no_duplicates_when_new_enquiries_arrive(): void
    {
        $expected = [];
        for ($i = 0; $i < 25; $i++) {
            $expected[] = $this->enquiry()->id;
        }
        $response = $this->actingAs($this->admin())->get('/admin')->assertOk();
        $first = $response->viewData('enquiries');
        $this->assertCount(20, $first);
        $this->enquiry();
        $second = $this->get($first->nextPageUrl())->assertOk()->viewData('enquiries');
        $this->assertCount(5, $second);
        $actual = array_merge($first->pluck('id')->all(), $second->pluck('id')->all());
        $this->assertSame(array_reverse($expected), $actual);
    }

    public function test_dashboard_uses_bounded_queries_and_direct_date_filter(): void
    {
        $date = now()->addDay()->toDateString();
        $match = $this->enquiry();
        $other = $this->enquiry();
        $other->forceFill(['status' => 'closed'])->save();
        $this->enquiry(['appointment_date' => now()->addDays(2)->toDateString()]);
        $this->actingAs($this->admin());
        DB::enableQueryLog();
        try {
            $response = $this->get('/admin?status=new&date='.$date)->assertOk();
            $this->assertSame([$match->id], $response->viewData('enquiries')->pluck('id')->all());
            $reads = collect(DB::getQueryLog())->pluck('query')->filter(
                fn (string $sql) => str_starts_with(strtolower($sql), 'select')
                    && (str_contains($sql, 'enquiries') || str_contains($sql, 'newsletter_subscribers'))
            );
            $this->assertCount(2, $reads);
            foreach ($reads as $sql) {
                $this->assertStringNotContainsString('count(', strtolower($sql));
                $this->assertStringNotContainsString('date(', strtolower($sql));
                $this->assertStringNotContainsString('offset', strtolower($sql));
                $this->assertStringContainsString('limit 21', strtolower($sql));
            }
        } finally {
            DB::disableQueryLog();
        }
    }

    public function test_large_exports_are_rejected_but_filtered_exports_work(): void
    {
        config(['performance.export_max_rows' => 2]);
        $this->enquiry(['name' => 'First']);
        $this->enquiry(['name' => 'Second']);
        $closed = $this->enquiry(['name' => 'Closed']);
        $closed->forceFill(['status' => 'closed'])->save();
        $this->actingAs($this->admin())->getJson(route('admin.enquiries.export'))
            ->assertUnprocessable()->assertJsonValidationErrors('export');
        $response = $this->get(route('admin.enquiries.export', ['status' => 'new']))
            ->assertOk()->assertDownload()->assertHeader('Cache-Control', 'no-store, private');
        $csv = $response->streamedContent();
        $this->assertStringContainsString('First', $csv);
        $this->assertStringContainsString('Second', $csv);
        $this->assertStringNotContainsString('Closed', $csv);
    }

    public function test_csv_exports_are_rate_limited_per_administrator(): void
    {
        $this->actingAs($this->admin());
        for ($i = 0; $i < 2; $i++) {
            $this->get(route('admin.enquiries.export'))->assertOk();
        }
        $this->get(route('admin.enquiries.export'))->assertTooManyRequests();
    }
}
