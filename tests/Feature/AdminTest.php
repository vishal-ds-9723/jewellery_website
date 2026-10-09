<?php

namespace Tests\Feature;

use App\Models\Enquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->is_admin = true;
        $user->save();

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

    public function test_dashboard_and_csv_require_administrator_access(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
        $this->get(route('admin.enquiries.export'))->assertRedirect(route('login'));
        $user = User::factory()->create();
        $this->actingAs($user)->get('/admin')->assertForbidden();
        $this->actingAs($user)->get(route('admin.enquiries.export'))->assertForbidden();
        $this->actingAs($user)->patch(route('admin.enquiries.update', $this->enquiry()), ['status' => 'confirmed'])->assertForbidden();
    }

    public function test_admin_login_and_logout(): void
    {
        $admin = $this->admin();
        $this->post(route('login.store'), ['email' => $admin->email, 'password' => 'password'])->assertRedirect(route('admin.index'));
        $this->assertAuthenticatedAs($admin);
        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_non_admin_cannot_sign_in_to_dashboard(): void
    {
        $user = User::factory()->create();
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_can_view_escaped_messages_and_update_status(): void
    {
        $enquiry = $this->enquiry(['message' => '<script>alert("test")</script>']);
        $this->actingAs($this->admin())->get('/admin')
            ->assertOk()->assertSee('&lt;script&gt;', false)->assertDontSee('<script>alert', false);
        $this->patch(route('admin.enquiries.update', $enquiry), ['status' => 'confirmed'])->assertRedirect();
        $this->assertDatabaseHas('enquiries', ['id' => $enquiry->id, 'status' => 'confirmed']);
        $this->patch(route('admin.enquiries.update', $enquiry), ['status' => 'invalid'])->assertSessionHasErrors('status');
    }

    public function test_csv_export_filters_records_and_neutralizes_formulas(): void
    {
        $this->enquiry(['name' => '=1+1']);
        $closed = $this->enquiry(['name' => 'Closed Customer']);
        $closed->status = 'closed';
        $closed->save();
        $response = $this->actingAs($this->admin())->get(route('admin.enquiries.export', ['status' => 'new']));
        $response->assertOk()->assertDownload();
        $this->assertStringContainsString("'=1+1", $response->streamedContent());
        $this->assertStringNotContainsString('Closed Customer', $response->streamedContent());
    }
}
