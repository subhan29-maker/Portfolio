<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

/**
 * The portfolio-wide settings shared by the public site and the dashboard:
 * the profile picture and the portfolio details (name, title, introduction,
 * contact details, social links).
 *
 * Everything here is a layered override: config/portfolio.php stays the
 * default so the site renders before anything is saved, and a stored value
 * wins once an administrator has edited it. The picture lives on the public
 * disk while its path lives in the settings table, so replacing it never
 * requires touching source code — and a path whose file has gone missing is
 * ignored, letting the site fall back to the configured default instead of
 * rendering a broken image.
 */
class PortfolioSettings
{
    public const PROFILE_IMAGE_PATH = 'profile_image_path';

    public const PROFILE_IMAGE_ALT = 'profile_image_alt';

    public const PROFILE_DETAILS = 'profile_details';

    /**
     * The keys an administrator can edit on the Profile screen.
     *
     * @var list<string>
     */
    public const DETAIL_KEYS = [
        'name',
        'role',
        'hero_label',
        'hero_headline',
        'hero_lead',
        'availability',
        'email',
        'phone',
        'location',
        'github',
        'linkedin',
        'x',
    ];

    /*
    |--------------------------------------------------------------------------
    | Profile picture
    |--------------------------------------------------------------------------
    */

    /**
     * The active profile picture, or null when none is configured.
     *
     * @return array{photo: string, photo_alt: string}|null
     */
    public function profileImage(): ?array
    {
        $path = trim((string) Setting::get(self::PROFILE_IMAGE_PATH));

        if ($path === '') {
            return null;
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($path)) {
            return null;
        }

        $alt = trim((string) Setting::get(self::PROFILE_IMAGE_ALT, ''));

        // Prefer direct public URLs that work even if the storage symlink is broken
        $relative = ltrim($path, '/');
        $url = null;

        // Try public/storage first (canonical with symlink)
        if (file_exists(public_path('storage/'.$relative))) {
            $url = asset('storage/'.$relative);
        } elseif (file_exists(public_path($relative))) {
            // Fallback direct in public/
            $url = asset($relative);
        } else {
            // Fall back to disk URL (no filesystem mutations in accessors)
            try {
                $url = url($disk->url($relative));
            } catch (\Throwable $e) {
                $url = url('storage/'.$relative);
            }
        }

        return [
            'photo' => $url,
            'photo_alt' => $alt !== ''
                ? $alt
                : (string) config('portfolio.profile.photo_alt'),
        ];
    }

    /**
     * Point the portfolio at a newly uploaded picture.
     */
    public function setProfileImage(string $path, ?string $alt = null): void
    {
        Setting::put(self::PROFILE_IMAGE_PATH, $path);
        Setting::put(self::PROFILE_IMAGE_ALT, filled($alt) ? trim($alt) : null);
    }

    /**
     * Forget the custom picture so the configured default is used again.
     *
     * The file itself is deleted by the service that stored it, and only
     * after this succeeds — so a failed delete can never leave a reference
     * to a file that is already gone.
     */
    public function clearProfileImage(): void
    {
        Setting::forget(self::PROFILE_IMAGE_PATH);
        Setting::forget(self::PROFILE_IMAGE_ALT);
    }

    /*
    |--------------------------------------------------------------------------
    | Portfolio details
    |--------------------------------------------------------------------------
    */

    /**
     * The stored details (empty until the dashboard is used for the first time).
     *
     * @return array<string, string>
     */
    public function details(): array
    {
        $decoded = json_decode((string) Setting::get(self::PROFILE_DETAILS, ''), true);

        if (! is_array($decoded)) {
            return [];
        }

        $details = [];

        foreach (self::DETAIL_KEYS as $key) {
            if (array_key_exists($key, $decoded)) {
                $details[$key] = trim((string) $decoded[$key]);
            }
        }

        return $details;
    }

    /**
     * Persist the edited details.
     *
     * @param  array<string, mixed>  $details
     */
    public function setDetails(array $details): void
    {
        $stored = [];

        foreach (self::DETAIL_KEYS as $key) {
            $stored[$key] = trim((string) ($details[$key] ?? ''));
        }

        Setting::put(self::PROFILE_DETAILS, json_encode($stored, JSON_UNESCAPED_SLASHES));
    }

    /**
     * Values for the Profile form: stored values first, configuration as the
     * default before anything has been saved.
     *
     * @return array<string, string>
     */
    public function formValues(): array
    {
        $defaults = (array) config('portfolio.profile');
        $stored = $this->details();

        $socials = collect((array) ($defaults['socials'] ?? []))
            ->keyBy(fn (array $social) => strtolower((string) ($social['label'] ?? '')));

        $values = [];

        foreach (self::DETAIL_KEYS as $key) {
            if ($key === 'github' || $key === 'linkedin' || $key === 'x') {
                $values[$key] = $stored[$key] ?? trim((string) ($socials[$key]['url'] ?? ''));

                continue;
            }

            $values[$key] = $stored[$key] ?? (string) ($defaults[$key] ?? '');
        }

        return $values;
    }

    /**
     * The profile every view needs: configuration, overridden by stored
     * details, overridden by the uploaded picture.
     *
     * @return array<string, mixed>
     */
    public function profile(): array
    {
        $profile = (array) config('portfolio.profile');
        $stored = $this->details();

        if ($stored !== []) {
            foreach (self::DETAIL_KEYS as $key) {
                if ($key === 'github' || $key === 'linkedin' || $key === 'x') {
                    continue;
                }

                $profile[$key] = $stored[$key];
            }

            // Once the details are owned by the dashboard, a social link only
            // appears when it has a value there — clearing it hides it.
            $profile['socials'] = array_values(array_filter([
                ['label' => 'GitHub', 'icon' => 'github', 'url' => $stored['github'] ?? ''],
                ['label' => 'LinkedIn', 'icon' => 'linkedin', 'url' => $stored['linkedin'] ?? ''],
                ['label' => 'X', 'icon' => 'x', 'url' => $stored['x'] ?? ''],
            ], fn (array $social) => filled($social['url'])));
        }

        if ($image = $this->profileImage()) {
            $profile['photo'] = $image['photo'];
            $profile['photo_alt'] = $image['photo_alt'];
        } elseif (filled($alt = trim((string) Setting::get(self::PROFILE_IMAGE_ALT)))) {
            // A saved description also applies to the default picture, so the
            // picture's alt text follows the author's identity even before a
            // photo has been uploaded.
            $profile['photo_alt'] = $alt;
        }

        return $profile;
    }
}
