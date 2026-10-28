<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Services\ProfileImageService;
use App\Support\PortfolioSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileImageTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_an_administrator_can_upload_a_profile_picture(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->admin())
            ->put(route('admin.profile.update'), [
                'name' => 'Subhan',
                'role' => 'Full-Stack Developer',
                'photo' => $this->fakeImage('portrait.png'),
                'photo_alt' => 'Portrait of Subhan at work',
            ]);

        $response->assertRedirect(route('admin.profile.edit'));

        $path = Setting::get(PortfolioSettings::PROFILE_IMAGE_PATH);

        $this->assertNotNull($path);
        $this->assertStringStartsWith('profile/', $path);
        Storage::disk('public')->assertExists($path);

        $picture = app(PortfolioSettings::class)->profileImage();

        $this->assertNotNull($picture);
        $this->assertSame('Portrait of Subhan at work', $picture['photo_alt']);
    }

    public function test_the_uploaded_picture_is_shown_on_the_public_pages(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())
            ->put(route('admin.profile.update'), [
                'name' => 'Subhan',
                'role' => 'Full-Stack Developer',
                'photo' => $this->fakeImage('portrait.png'),
                'photo_alt' => 'Portrait of Subhan at work',
            ]);

        $url = app(PortfolioSettings::class)->profileImage()['photo'];

        foreach (['/', '/about'] as $page) {
            $this->get($page)
                ->assertOk()
                ->assertSee($url, false)
                ->assertSee('Portrait of Subhan at work', false);
        }
    }

    public function test_the_default_picture_is_used_when_none_was_uploaded(): void
    {
        $default = (string) config('portfolio.profile.photo');

        $this->get('/')->assertOk()->assertSee($default, false);
    }

    public function test_a_missing_file_falls_back_to_the_default(): void
    {
        // The setting points at a file that is not there any more; the site
        // must keep rendering the configured default instead of a broken tag.
        Setting::put(PortfolioSettings::PROFILE_IMAGE_PATH, 'profile/gone.png');

        $default = (string) config('portfolio.profile.photo');

        $this->get('/')->assertOk()->assertSee($default, false);
    }

    public function test_replacing_the_picture_removes_the_previous_file(): void
    {
        Storage::fake('public');

        $admin = $this->admin();

        $this->actingAs($admin)->put(route('admin.profile.update'), [
            'name' => 'Subhan',
            'role' => 'Full-Stack Developer',
            'photo' => $this->fakeImage('first.png'),
            'photo_alt' => 'First picture',
        ]);

        $first = Setting::get(PortfolioSettings::PROFILE_IMAGE_PATH);

        $this->actingAs($admin)->put(route('admin.profile.update'), [
            'name' => 'Subhan',
            'role' => 'Full-Stack Developer',
            'photo' => $this->fakeImage('second.png'),
            'photo_alt' => 'Second picture',
        ]);

        $second = Setting::get(PortfolioSettings::PROFILE_IMAGE_PATH);

        $this->assertNotSame($first, $second);
        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($second);
    }

    public function test_a_failed_upload_keeps_the_current_picture(): void
    {
        Storage::fake('public');

        $admin = $this->admin();

        $this->actingAs($admin)->put(route('admin.profile.update'), [
            'name' => 'Subhan',
            'role' => 'Full-Stack Developer',
            'photo' => $this->fakeImage('good.png'),
        ]);

        $current = Setting::get(PortfolioSettings::PROFILE_IMAGE_PATH);

        $this->actingAs($admin)
            ->from(route('admin.profile.edit'))
            ->put(route('admin.profile.update'), [
                'name' => 'Subhan',
                'role' => 'Full-Stack Developer',
                'photo' => UploadedFile::fake()->create('notes.txt', 4, 'text/plain'),
            ])
            ->assertSessionHasErrors('photo');

        Storage::disk('public')->assertExists($current);
        $this->assertSame($current, Setting::get(PortfolioSettings::PROFILE_IMAGE_PATH));
    }

    public function test_oversized_uploads_are_rejected(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())
            ->put(route('admin.profile.update'), [
                'name' => 'Subhan',
                'role' => 'Full-Stack Developer',
                'photo' => $this->fakeLargeImage('huge.png'),
            ])
            ->assertSessionHasErrors('photo');

        $this->assertNull(Setting::get(PortfolioSettings::PROFILE_IMAGE_PATH));
    }

    public function test_the_picture_can_be_reset_to_the_default(): void
    {
        Storage::fake('public');

        $admin = $this->admin();

        $this->actingAs($admin)->put(route('admin.profile.update'), [
            'name' => 'Subhan',
            'role' => 'Full-Stack Developer',
            'photo' => $this->fakeImage('portrait.png'),
        ]);

        $path = Setting::get(PortfolioSettings::PROFILE_IMAGE_PATH);

        $this->actingAs($admin)
            ->delete(route('admin.profile.picture.destroy'))
            ->assertRedirect();

        $this->assertNull(Setting::get(PortfolioSettings::PROFILE_IMAGE_PATH));
        Storage::disk('public')->assertMissing($path);

        $default = (string) config('portfolio.profile.photo');
        $this->get('/')->assertOk()->assertSee($default, false);
    }

    public function test_resetting_without_an_uploaded_picture_is_harmless(): void
    {
        $this->actingAs($this->admin())
            ->delete(route('admin.profile.picture.destroy'))
            ->assertRedirect();

        $this->assertNull(Setting::get(PortfolioSettings::PROFILE_IMAGE_PATH));
        $this->get('/')->assertOk();
    }

    public function test_profile_management_is_admin_only(): void
    {
        $this->get(route('admin.profile.edit'))->assertRedirect(route('login'));
        $this->put(route('admin.profile.update'), [])->assertRedirect(route('login'));
        $this->delete(route('admin.profile.picture.destroy'))->assertRedirect(route('login'));

        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.profile.edit'))->assertForbidden();
        $this->actingAs($user)->delete(route('admin.profile.picture.destroy'))->assertForbidden();
    }

    public function test_the_storage_service_never_deletes_a_file_it_does_not_own(): void
    {
        Storage::fake('public');

        Storage::disk('public')->put('profile/portrait.png', 'real-image');

        $service = app(ProfileImageService::class);

        // Absolute URLs and site-root paths point at assets this service does
        // not own, so they are left completely alone.
        $service->delete('https://example.com/portrait.png');
        $service->delete('/images/profile/profile-placeholder.svg');
        $service->delete('');

        Storage::disk('public')->assertExists('profile/portrait.png');
    }
}
