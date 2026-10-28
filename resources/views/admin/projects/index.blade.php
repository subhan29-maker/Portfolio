@extends('layouts.admin')

@section('title', 'Projects')

@section('actions')
    <a class="btn btn--primary btn--sm" href="{{ route('admin.projects.create') }}">
        <x-icon name="plus" />
        New project
    </a>
@endsection

@section('content')
    @php
        // The three filter fields the toolbar offers; used to decide whether
        // a "clear filters" shortcut is worth showing.
        $hasFilters = filled(request('q')) || filled(request('status')) || filled(request('category'));
    @endphp
    <section class="admin-section">
        <div class="admin-card">
            <form method="GET" action="{{ route('admin.projects.index') }}" class="admin-toolbar">
                <x-form.input
                    name="q"
                    label="Search"
                    type="search"
                    :value="request('q')"
                    placeholder="Title, summary or category"
                />

                <x-form.select
                    name="status"
                    label="Status"
                    wrapper-class="field--narrow"
                    :options="['' => 'Any status'] + \App\Enums\ProjectStatus::options()"
                    :value="request('status')"
                />

                <x-form.select
                    name="category"
                    label="Category"
                    wrapper-class="field--narrow"
                    :options="['' => 'Any category'] + $categories->mapWithKeys(fn ($category) => [$category => $category])->all()"
                    :value="request('category')"
                />

                <div class="admin-toolbar__actions">
                    <button class="btn btn--ghost btn--sm" type="submit">
                        <x-icon name="search" />
                        Filter
                    </button>

                    @if ($hasFilters)
                        <a class="btn btn--ghost btn--sm" href="{{ route('admin.projects.index') }}">Clear</a>
                    @endif
                </div>
            </form>

            @if ($projects->isEmpty())
                <div class="admin-card__body">
                    <div class="empty-state">
                        @if ($hasFilters)
                            <h3>Nothing matches those filters</h3>
                            <p>Try a different search term, or clear the filters to see every project.</p>
                            <a class="btn btn--ghost btn--sm" href="{{ route('admin.projects.index') }}">Clear filters</a>
                        @else
                            <h3>No projects yet</h3>
                            <p>Add your first project, then publish it when you are ready for it to appear on the public portfolio.</p>
                            <a class="btn btn--primary btn--sm" href="{{ route('admin.projects.create') }}">
                                <x-icon name="plus" />
                                Add a project
                            </a>
                        @endif
                    </div>
                </div>
            @else
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th scope="col"><span class="sr-only">Preview</span></th>
                                <th scope="col">Project</th>
                                <th scope="col">Status</th>
                                <th scope="col">Position</th>
                                <th scope="col">Updated</th>
                                <th scope="col"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($projects as $project)
                                <tr>
                                    <td>
                                        @if ($project->coverUrl(false))
                                            <img class="admin-table__thumb" src="{{ $project->coverUrl(false) }}" alt="" loading="lazy">
                                        @endif
                                    </td>

                                    <td>
                                        <p class="admin-table__title">
                                            <a href="{{ route('admin.projects.edit', $project) }}">{{ $project->title }}</a>

                                            @if ($project->isFeatured())
                                                <span class="badge badge--accent">Featured</span>
                                            @endif

                                            @if ($project->isDemo())
                                                <span class="badge badge--demo">Demo</span>
                                            @endif
                                        </p>
                                        <p class="admin-table__meta">/{{ $project->slug }}</p>
                                    </td>

                                    <td>
                                        <span class="badge {{ $project->status->badgeModifier() }}">{{ $project->status->label() }}</span>
                                    </td>

                                    <td class="admin-table__meta">{{ $project->sort_order }}</td>

                                    <td class="admin-table__meta">{{ $project->updated_at->diffForHumans() }}</td>

                                    <td>
                                        <div class="admin-table__actions">
                                            @if ($project->isPublished())
                                                <a class="btn btn--ghost btn--sm" href="{{ route('projects.show', $project->slug) }}" target="_blank" rel="noopener">
                                                    <x-icon name="eye" />
                                                    View
                                                </a>

                                                <form method="POST" action="{{ route('admin.projects.unpublish', $project) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button class="btn btn--ghost btn--sm" type="submit">
                                                        <x-icon name="eye-off" />
                                                        Unpublish
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('admin.projects.publish', $project) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button class="btn btn--ghost btn--sm" type="submit">
                                                        <x-icon name="eye" />
                                                        Publish
                                                    </button>
                                                </form>
                                            @endif

                                            <a class="btn btn--ghost btn--sm" href="{{ route('admin.projects.edit', $project) }}">Edit</a>

                                            <form
                                                method="POST"
                                                action="{{ route('admin.projects.destroy', $project) }}"
                                                data-confirm="Delete {{ $project->title }} permanently? This also removes its uploaded images. This cannot be undone."
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn--ghost btn--sm" type="submit">
                                                    <x-icon name="trash" />
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{ $projects->links() }}
            @endif
        </div>
    </section>
@endsection
