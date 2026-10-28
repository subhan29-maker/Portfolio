<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_public_site_stays_public(): void
    {
        // The homepage is the portfolio, never the dashboard or a redirect.
        $this->get('/')->assertOk()->assertSeeText('Subhan');
        $this->get('/login')->assertOk();
    }

    public function test_the_login_screen_is_a_proper_page(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Sign in')
            ->assertSee('Administrator access', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false)
            ->assertSee('Back to the portfolio');
    }

    public function test_guests_are_redirected_to_the_login_screen(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
        $this->get('/admin/dashboard')->assertRedirect(route('login'));
        $this->get('/admin/projects')->assertRedirect(route('login'));
        $this->get('/admin/projects/create')->assertRedirect(route('login'));
        $this->get('/admin/profile')->assertRedirect(route('login'));
    }

    public function test_administrators_can_authenticate(): void
    {
        $admin = User::factory()->admin()->create(['password' => 'secret-password']);

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_a_returning_guest_is_sent_back_to_where_they_wanted_to_go(): void
    {
        $admin = User::factory()->admin()->create(['password' => 'secret-password']);

        $intended = URL::to('/admin/projects');

        $this->get('/admin/projects')->assertRedirect(route('login'));

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->assertRedirect($intended);
    }

    public function test_the_session_is_regenerated_on_sign_in(): void
    {
        $admin = User::factory()->admin()->create(['password' => 'secret-password']);

        $this->get('/login');

        $before = session()->getId();

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ]);

        $this->assertNotSame($before, session()->getId());
    }

    public function test_credentials_are_validated(): void
    {
        $this->post('/login', ['email' => 'not-an-email', 'password' => 'x'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_invalid_credentials_are_rejected_with_a_generic_message(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->from('/login')->post('/login', [
            'email' => $admin->email,
            'password' => 'the-wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $response->assertSessionDoesntHaveErrors('password');
        $this->assertGuest();

        // The same wording is used for an address that does not exist at all,
        // so the form never reveals which accounts are registered.
        $this->post('/login', [
            'email' => 'nobody@example.com',
            'password' => 'whatever',
        ])->assertSessionHasErrors([
            'email' => 'These credentials do not match our records.',
        ]);
    }

    public function test_non_administrators_cannot_sign_in(): void
    {
        $user = User::factory()->create(['password' => 'secret-password']);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'secret-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_authenticated_non_administrators_are_forbidden(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin')->assertForbidden();
        $this->actingAs($user)->get('/admin/projects')->assertForbidden();
        $this->actingAs($user)->get('/admin/profile')->assertForbidden();
    }

    public function test_administrators_can_sign_out(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post('/logout')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_signing_out_invalidates_the_session(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/logout');

        $this->get('/admin/dashboard')->assertRedirect(route('login'));
    }

    public function test_repeated_login_attempts_are_throttled(): void
    {
        $admin = User::factory()->admin()->create();

        foreach (range(1, 10) as $attempt) {
            $this->post('/login', [
                'email' => $admin->email,
                'password' => 'wrong-password-'.$attempt,
            ]);
        }

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->assertStatus(429);
    }

    public function test_the_login_form_is_not_offered_once_signed_in(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('login'))->assertRedirect(route('admin.dashboard'));
    }
}
