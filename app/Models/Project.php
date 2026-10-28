<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * A portfolio project as stored by this application.
 *
 * The public website reads these rows through App\Portfolio\ProjectRepository,
 * which maps them onto the immutable project shape the Blade views expect.
 */
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'summary',
        'description',
        'problem',
        'purpose',
        'features',
        'highlights',
        'contribution',
        'role',
        'year',
        'technologies',
        'category',
        'live_demo_url',
        'repository_url',
        'is_featured',
        'is_demo',
        'status',
        'cover_image',
        'gallery',
        'sort_order',
        'published_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'technologies' => 'array',
            'features' => 'array',
            'highlights' => 'array',
            'gallery' => 'array',
            'is_featured' => 'boolean',
            'is_demo' => 'boolean',
            'status' => ProjectStatus::class,
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $project): void {
            if (blank($project->slug)) {
                $project->slug = static::uniqueSlug((string) $project->title, $project->id);
            }

            if ($project->status === ProjectStatus::Published && blank($project->published_at)) {
                $project->published_at = now();
            }
        });
    }

    /**
     * Generate a URL-safe slug that is guaranteed to be unique in the table.
     */
    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = (string) str($title)->slug() ?: 'project';
        $slug = $base;
        $suffix = 1;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->exists()
        ) {
            $slug = $base.'-'.(++$suffix);
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /*
    |--------------------------------------------------------------------------
    | Query scopes
    |--------------------------------------------------------------------------
    */

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ProjectStatus::Published);
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', ProjectStatus::Draft);
    }

    public function scopeUnpublished(Builder $query): Builder
    {
        return $query->where('status', ProjectStatus::Unpublished);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * The order visitors see: the explicit display position, falling back to
     * creation order for projects that have never been positioned.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isPublished(): bool
    {
        return $this->status === ProjectStatus::Published;
    }

    public function isFeatured(): bool
    {
        return (bool) $this->is_featured;
    }

    public function isDemo(): bool
    {
        return (bool) $this->is_demo;
    }

    /**
     * Resolve a stored path on the public disk to an absolute URL.
     *
     * Values that already are absolute URLs are returned untouched, so a bad
     * row can never be prefixed twice.
     */
    protected function mediaUrl(?string $path, bool $absolute = true): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (preg_match('#^(https?:)?//#i', $path) === 1) {
            return $path;
        }

        $url = Storage::disk('public')->url(ltrim($path, '/'));

        return $absolute ? url($url) : $url;
    }

    public function coverUrl(bool $absolute = true): ?string
    {
        return $this->mediaUrl($this->cover_image, $absolute);
    }

    /**
     * @return list<string>
     */
    public function galleryUrls(bool $absolute = true): array
    {
        $urls = [];

        foreach ((array) ($this->gallery ?? []) as $path) {
            if (is_string($path) && ($url = $this->mediaUrl($path, $absolute))) {
                $urls[] = $url;
            }
        }

        return $urls;
    }
}
