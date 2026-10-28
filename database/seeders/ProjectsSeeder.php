<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Copy the starter projects from config/portfolio.php into the database.
 *
 * The live site always reads the projects table, so a fresh install needs
 * this to have something real to show. It only ever creates what is missing:
 * a slug that already exists is left untouched, which makes re-running it
 * safe next to projects you have already written or imported.
 */
class ProjectsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ((array) config('portfolio.projects', []) as $index => $entry) {
            $slug = (string) ($entry['slug'] ?? '');

            if ($slug === '' || Project::query()->where('slug', $slug)->exists()) {
                continue;
            }

            $isDemo = (bool) ($entry['is_demo'] ?? false);

            Project::create([
                'slug' => $slug,
                'title' => (string) ($entry['title'] ?? $slug),
                'summary' => (string) ($entry['summary'] ?? ''),
                'description' => (string) ($entry['overview'] ?? ''),
                'problem' => filled($entry['problem'] ?? null) ? (string) $entry['problem'] : null,
                'purpose' => filled($entry['purpose'] ?? null) ? (string) $entry['purpose'] : null,
                'features' => array_values(array_filter((array) ($entry['features'] ?? []))),
                'highlights' => array_values(array_filter((array) ($entry['highlights'] ?? []))),
                'contribution' => filled($entry['contribution'] ?? null) ? (string) $entry['contribution'] : null,
                'role' => filled($entry['role'] ?? null) ? (string) $entry['role'] : null,
                'year' => filled($entry['year'] ?? null) ? (string) $entry['year'] : null,
                'technologies' => array_values(array_filter((array) ($entry['technologies'] ?? []))),
                'live_demo_url' => filled($entry['demo_url'] ?? null) ? (string) $entry['demo_url'] : null,
                'repository_url' => filled($entry['repo_url'] ?? null) ? (string) $entry['repo_url'] : null,
                'is_featured' => ! $isDemo,
                'is_demo' => $isDemo,
                'status' => 'published',
                'cover_image' => $this->publishAsset((string) ($entry['cover'] ?? '')),
                'gallery' => $this->publishGallery((array) ($entry['gallery'] ?? [])),
                'sort_order' => ($index + 1) * 10,
                'published_at' => now(),
            ]);
        }
    }

    /**
     * Copy a site image into public storage so the dashboard can manage it,
     * and return the storage path it was copied to.
     */
    private function publishAsset(string $path): ?string
    {
        $path = trim($path);

        if ($path === '' || preg_match('#^(https?:)?//#i', $path) === 1) {
            return $path === '' ? null : $path;
        }

        return $this->copyToStorage($path) ?? $path;
    }

    /**
     * Copy every gallery image into public storage and return storage paths.
     *
     * @param  array<int, mixed>  $gallery
     * @return list<string>
     */
    private function publishGallery(array $gallery): array
    {
        $paths = [];

        foreach ($gallery as $shot) {
            $src = is_array($shot) ? (string) ($shot['src'] ?? '') : (string) $shot;

            if (filled($src) && ($stored = $this->publishAsset($src))) {
                $paths[] = $stored;
            }
        }

        return array_values(array_unique($paths));
    }

    /**
     * Copy a file from public/ into the public disk, keeping the file name.
     */
    private function copyToStorage(string $publicPath): ?string
    {
        $relative = ltrim($publicPath, '/');
        $source = public_path($relative);

        if (! is_file($source)) {
            return null;
        }

        $disk = Storage::disk('public');

        if ($disk->exists($relative)) {
            return $relative;
        }

        $disk->put($relative, file_get_contents($source));

        return $relative;
    }
}
