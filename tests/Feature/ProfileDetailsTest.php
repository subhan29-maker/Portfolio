<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The Portfolio details on /admin/profile replace the hardcoded identity
 * content on the public pages once saved.
 */
class ProfileDetailsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    /**
     * @param  array<string, string>  $details
     */
    private function saveDetails(array $details): void
    {
        $this->actingAs($this->admin())
            ->put(route('admin.profile.update'), array_merge([
                'name' => 'Subhan',
                'role' => 'Full-Stack Developer',
            ], $details))
            ->assertRedirect(route('admin.profile.edit'));
    }

    public function test_the_profile_form_starts_from_the_configured_values(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.profile.edit'))
            ->assertOk()
            ->assertSee('Profile picture')
            ->assertSee('Portfolio details')
            ->assertSee('Contact & social links')
            ->assertSee((string) config('portfolio.profile.name'))
            ->assertSee((string) config('portfolio.profile.role'))
            ->assertSee('Save profile');
    }

    public function test_saved_details_replace_the_hardcoded_identity_content(): void
    {
        $this->saveDetails([
            'name' => 'Aurangzeb Khan',
            'role' => 'Senior Laravel Developer',
            'hero_headline' => 'Building reliable web platforms',
            'email' => 'hello@example.com',
            'photo_alt' => 'Portrait of Aurangzeb Khan at work',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Aurangzeb Khan')
            ->assertSee('Senior Laravel Developer')
            ->assertSee('Building reliable web platforms')
            ->assertSee('hello@example.com')
            ->assertDontSee((string) config('portfolio.profile.name'), false);

        // The meta descriptions on the other public pages follow the name too.
        $this->get('/about')
            ->assertOk()
            ->assertSee('About Aurangzeb Khan:', false)
            ->assertDontSee((string) config('portfolio.profile.name'), false);

        $this->get('/skills')
            ->assertOk()
            ->assertSee('Skills and tools Aurangzeb Khan works with:', false)
            ->assertDontSee((string) config('portfolio.profile.name'), false);
    }

    public function test_clearing_a_contact_detail_hides_it(): void
    {
        // Start from a stored state that has a phone number, then clear it.
        $this->saveDetails(['phone' => '+1 555 0100', 'location' => 'Karachi']);

        $this->get('/contact')
            ->assertOk()
            ->assertSee('+1 555 0100')
            ->assertSee('Karachi');

        $this->saveDetails(['phone' => '', 'location' => '']);

        $this->get('/contact')
            ->assertOk()
            ->assertDontSee('+1 555 0100')
            ->assertDontSee('Karachi');
    }

    public function test_social_links_are_hidden_until_they_have_a_value(): void
    {
        $defaults = collect(config('portfolio.profile.socials'))->pluck('url');

        $this->saveDetails(['github' => '', 'linkedin' => '', 'x' => '']);

        foreach ($defaults as $url) {
            $this->get('/')->assertOk()->assertDontSee($url, false);
        }

        $this->saveDetails(['github' => 'https://github.com/aurangzeb']);

        $this->get('/')
            ->assertOk()
            ->assertSee('https://github.com/aurangzeb', false)
            ->assertSee('GitHub');
    }

    public function test_invalid_social_links_are_rejected(): void
    {
        $this->actingAs($this->admin())
            ->put(route('admin.profile.update'), [
                'name' => 'Subhan',
                'role' => 'Full-Stack Developer',
                'github' => 'github.com/missing-scheme',
                'linkedin' => 'not a url',
                'x' => 'also not a url',
            ])
            ->assertSessionHasErrors(['github', 'linkedin', 'x']);
    }

    public function test_name_and_role_are_required(): void
    {
        $this->actingAs($this->admin())
            ->put(route('admin.profile.update'), [
                'name' => '',
                'role' => '',
            ])
            ->assertSessionHasErrors(['name', 'role']);
    }

    public function test_the_dashboard_is_not_linked_from_the_public_pages(): void
    {
        $this->saveDetails(['name' => 'Subhan']);

        foreach (['/', '/about', '/skills', '/projects', '/contact'] as $page) {
            $this->get($page)
                ->assertOk()
                ->assertDontSee('admin/profile', false)
                ->assertDontSee('Sign in', false);
        }
    }
}
