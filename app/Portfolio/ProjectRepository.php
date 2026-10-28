<?php

namespace App\Portfolio;

use App\Models\Project as ProjectModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

/**
 * Provides the portfolio projects.
 *
 * The database is the single source of truth: only published projects are
 * handed to the public pages, in the same display order the admin sees
 * (the explicit "sort position" field, then creation order). Rows are mapped
 * onto the immutable project shape the Blade views expect, and the whole list
 * is resolved once per request so the index page, related projects and the
 * detail page do no repeated work.
 */
final class ProjectRepository
{
    /** @var Collection<int, Project>|null */
    private ?Collection $projects = null;

    /**
     * @return Collection<int, Project>
     */
    public function all(): Collection
    {
        return $this->projects ??= ProjectModel::query()
            ->published()
            ->ordered()
            ->get()
            ->map(fn (ProjectModel $project) => $this->toProject($project))
            ->values();
    }

    /**
     * Look a published project up by its public URL slug.
     *
     * Draft and unpublished projects behave exactly like unknown slugs: the
     * caller gets a ModelNotFoundException, which Laravel renders as a 404.
     *
     * @throws ModelNotFoundException
     */
    public function findOrFail(string $slug): Project
    {
        $found = $this->all()->firstWhere('slug', $slug);

        if ($found instanceof Project) {
            return $found;
        }

        throw (new ModelNotFoundException)->setModel(ProjectModel::class, [$slug]);
    }

    /**
     * @return Collection<int, Project>
     */
    public function take(int $limit): Collection
    {
        return $this->all()->take($limit);
    }

    /**
     * Projects that share at least one technology with the given project.
     *
     * @return Collection<int, Project>
     */
    public function related(Project $project, int $limit = 3): Collection
    {
        return $this->all()
            ->reject(fn (Project $candidate) => $candidate->slug === $project->slug)
            ->sortByDesc(fn (Project $candidate) => count(array_intersect($candidate->technologies, $project->technologies)))
            ->take($limit)
            ->values();
    }

    /**
     * True while every listed project is still a demo placeholder.
     */
    public function onlyContainsDemoProjects(): bool
    {
        return $this->all()->isNotEmpty() && $this->all()->every(fn (Project $project) => $project->isDemo);
    }

    /**
     * Map a database row onto the shape the views use.
     */
    private function toProject(ProjectModel $project): Project
    {
        $gallery = collect($project->galleryUrls(false))
            ->map(fn (string $src) => [
                'src' => $src,
                'alt' => $project->title.' — project screenshot',
            ])
            ->all();

        return new Project(
            slug: $project->slug,
            title: $project->title,
            tagline: (string) $project->summary,
            summary: (string) $project->summary,
            overview: (string) $project->description,
            problem: $project->problem,
            purpose: $project->purpose,
            features: array_values((array) ($project->features ?? [])),
            highlights: array_values((array) ($project->highlights ?? [])),
            technologies: array_values((array) ($project->technologies ?? [])),
            contribution: $project->contribution,
            cover: (string) $project->coverUrl(false),
            gallery: $gallery,
            demoUrl: $project->live_demo_url,
            repoUrl: $project->repository_url,
            year: $project->year,
            role: $project->role,
            isDemo: $project->isDemo(),
        );
    }
}
