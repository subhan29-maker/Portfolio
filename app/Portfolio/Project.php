<?php

namespace App\Portfolio;

/**
 * A single portfolio project, as the public pages read it.
 *
 * The dashboard stores projects in the database; `ProjectRepository` maps each
 * row onto this shape so the public controllers and views never depend on
 * storage details.
 */
final readonly class Project
{
    /**
     * @param  list<string>  $features
     * @param  list<string>  $highlights
     * @param  list<string>  $technologies
     * @param  list<array{src: string, alt: string}>  $gallery
     */
    public function __construct(
        public string $slug,
        public string $title,
        public string $tagline,
        public string $summary,
        public string $overview,
        public ?string $problem,
        public ?string $purpose,
        public array $features,
        public array $highlights,
        public array $technologies,
        public ?string $contribution,
        public string $cover,
        public array $gallery,
        public ?string $demoUrl,
        public ?string $repoUrl,
        public ?string $year,
        public ?string $role,
        public bool $isDemo,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            slug: (string) $data['slug'],
            title: (string) $data['title'],
            tagline: (string) ($data['tagline'] ?? ''),
            summary: (string) ($data['summary'] ?? ''),
            overview: (string) ($data['overview'] ?? ''),
            problem: filled($data['problem'] ?? null) ? (string) $data['problem'] : null,
            purpose: filled($data['purpose'] ?? null) ? (string) $data['purpose'] : null,
            features: array_values(array_filter((array) ($data['features'] ?? []))),
            highlights: array_values(array_filter((array) ($data['highlights'] ?? []))),
            technologies: array_values(array_filter((array) ($data['technologies'] ?? []))),
            contribution: filled($data['contribution'] ?? null) ? (string) $data['contribution'] : null,
            cover: (string) ($data['cover'] ?? ''),
            gallery: array_values((array) ($data['gallery'] ?? [])),
            demoUrl: filled($data['demo_url'] ?? null) ? (string) $data['demo_url'] : null,
            repoUrl: filled($data['repo_url'] ?? null) ? (string) $data['repo_url'] : null,
            year: filled($data['year'] ?? null) ? (string) $data['year'] : null,
            role: filled($data['role'] ?? null) ? (string) $data['role'] : null,
            isDemo: (bool) ($data['is_demo'] ?? false),
        );
    }

    /**
     * Images shown on the detail page: the cover followed by any extra shots.
     *
     * @return list<array{src: string, alt: string}>
     */
    public function galleryWithCover(): array
    {
        return [
            ['src' => $this->cover, 'alt' => $this->title.' — project screenshot'],
            ...$this->gallery,
        ];
    }

    public function hasExternalLinks(): bool
    {
        return filled($this->demoUrl) || filled($this->repoUrl);
    }
}
