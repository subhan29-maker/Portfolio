<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProjectStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectRequest;
use App\Models\Project;
use App\Services\ProjectMediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function __construct(
        private ProjectMediaService $media,
    ) {}

    /**
     * Paginated, filterable project list.
     */
    public function index(Request $request): View
    {
        $projects = Project::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->input('q').'%';

                $query->where(fn ($inner) => $inner
                    ->where('title', 'like', $term)
                    ->orWhere('summary', 'like', $term)
                    ->orWhere('category', 'like', $term));
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->input('category')))
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.projects.index', [
            'projects' => $projects,
            'categories' => Project::query()
                ->whereNotNull('category')
                ->distinct()
                ->orderBy('category')
                ->pluck('category'),
            'nextSortOrder' => $this->nextSortOrder(),
        ]);
    }

    public function create(): View
    {
        return view('admin.projects.create', [
            'project' => null,
            'nextSortOrder' => $this->nextSortOrder(),
        ]);
    }

    public function store(ProjectRequest $request): RedirectResponse
    {
        $data = $this->validatedAttributes($request);

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $this->media->storeCover($request->file('cover_image'));
        }

        if ($request->hasFile('gallery')) {
            $data['gallery'] = collect($request->file('gallery'))
                ->map(fn (UploadedFile $file) => $this->media->storeGalleryImage($file))
                ->all();
        }

        $project = Project::create($data);

        return redirect()
            ->route('admin.projects.edit', $project)
            ->with('status', 'Project "'.$project->title.'" was created.');
    }

    public function show(Project $project): View
    {
        return view('admin.projects.show', [
            'project' => $project,
        ]);
    }

    public function edit(Project $project): View
    {
        return view('admin.projects.edit', [
            'project' => $project,
            'nextSortOrder' => $project->sort_order,
        ]);
    }

    public function update(ProjectRequest $request, Project $project): RedirectResponse
    {
        $data = $this->validatedAttributes($request, $project);

        // Cover: explicit removal, then optional replacement.
        if ($request->boolean('remove_cover') && $project->cover_image) {
            $this->media->delete($project->cover_image);
            $data['cover_image'] = null;
        }

        if ($request->hasFile('cover_image')) {
            $this->media->delete($project->cover_image);
            $data['cover_image'] = $this->media->storeCover($request->file('cover_image'));
        }

        // Gallery: remove selected images, then append any new uploads.
        $gallery = (array) ($project->gallery ?? []);

        foreach ((array) $request->input('remove_gallery', []) as $path) {
            if (in_array($path, $gallery, true)) {
                $this->media->delete($path);
                $gallery = array_values(array_diff($gallery, [$path]));
            }
        }

        foreach ((array) $request->file('gallery', []) as $file) {
            if ($file instanceof UploadedFile) {
                $gallery[] = $this->media->storeGalleryImage($file);
            }
        }

        $data['gallery'] = array_values($gallery);

        $project->update($data);

        return redirect()
            ->route('admin.projects.edit', $project)
            ->with('status', 'Project "'.$project->title.'" was updated.');
    }

    public function publish(Project $project): RedirectResponse
    {
        $project->update([
            'status' => ProjectStatus::Published,
            'published_at' => $project->published_at ?? now(),
        ]);

        return back()->with('status', 'Project "'.$project->title.'" is now published.');
    }

    public function unpublish(Project $project): RedirectResponse
    {
        $project->update(['status' => ProjectStatus::Unpublished]);

        return back()->with('status', 'Project "'.$project->title.'" was unpublished.');
    }

    public function destroyGalleryImage(Project $project, int $index): RedirectResponse
    {
        $gallery = (array) ($project->gallery ?? []);

        if (array_key_exists($index, $gallery)) {
            $this->media->delete($gallery[$index]);
            unset($gallery[$index]);

            $project->update(['gallery' => array_values($gallery)]);
        }

        return back()->with('status', 'Gallery image removed.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $title = $project->title;

        $this->media->delete($project->cover_image);

        foreach ((array) ($project->gallery ?? []) as $path) {
            $this->media->delete($path);
        }

        $project->delete();

        return redirect()
            ->route('admin.projects.index')
            ->with('status', 'Project "'.$title.'" was deleted.');
    }

    /**
     * Attributes shared by create and update.
     *
     * @return array<string, mixed>
     */
    private function validatedAttributes(ProjectRequest $request, ?Project $project = null): array
    {
        $data = [
            'title' => $request->string('title')->trim()->value(),
            'summary' => $request->input('summary'),
            'description' => $request->input('description'),
            'problem' => $request->input('problem'),
            'purpose' => $request->input('purpose'),
            'features' => $this->parseLines($request->input('features')),
            'highlights' => $this->parseLines($request->input('highlights')),
            'contribution' => $request->input('contribution'),
            'role' => $request->input('role'),
            'year' => $request->input('year'),
            'technologies' => $this->parseTechnologies($request->input('technologies')),
            'category' => $request->input('category'),
            'live_demo_url' => $request->input('live_demo_url'),
            'repository_url' => $request->input('repository_url'),
            'is_featured' => $request->boolean('is_featured'),
            'is_demo' => $request->boolean('is_demo'),
            'status' => $request->input('status'),
        ];

        // Display position: whatever was typed, otherwise keep the current one,
        // otherwise append to the end of the public list.
        $data['sort_order'] = $request->filled('sort_order')
            ? max(0, (int) $request->input('sort_order'))
            : (int) ($project?->sort_order ?? $this->nextSortOrder());

        // Only overwrite the slug when one was supplied, so editing a project
        // never silently changes its public URL.
        if ($request->filled('slug')) {
            $data['slug'] = $request->string('slug')->trim()->value();
        }

        return $data;
    }

    /**
     * The next free display position (1 for the first project).
     */
    private function nextSortOrder(): int
    {
        return (int) Project::query()->max('sort_order') + 1;
    }

    /**
     * Turn a comma / newline separated string into a clean list.
     *
     * Used for short values (technologies) where commas are natural
     * separators.
     *
     * @return list<string>
     */
    private function parseTechnologies(?string $value): array
    {
        return $this->cleanList(preg_split('/[,\n]+/', (string) $value));
    }

    /**
     * Turn a newline separated string into a clean list.
     *
     * Features and highlights are full sentences that may contain commas, so
     * only line breaks separate them.
     *
     * @return list<string>
     */
    private function parseLines(?string $value): array
    {
        return $this->cleanList(preg_split('/\r?\n/', (string) $value));
    }

    /**
     * @param  array<int, string>|false  $items
     * @return list<string>
     */
    private function cleanList(array|false $items): array
    {
        return array_values(
            collect($items)
                ->map(fn (?string $item) => trim((string) $item))
                ->filter()
                ->unique()
                ->values()
                ->all()
        );
    }
}
