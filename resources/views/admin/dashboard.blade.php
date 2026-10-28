@extends('layouts.admin')

@section('title', 'Dashboard')

@section('actions')
    <a class="btn btn--primary btn--sm" href="{{ route('admin.projects.create') }}">
        <x-icon name="plus" />
        New project
    </a>
@endsection

@section('content')
    <section class="admin-section" aria-label="Project totals">
        <div class="admin-stats">
            <div class="admin-stat">
                <p class="admin-stat__label">Total projects</p>
                <p class="admin-stat__value">{{ $counts['total'] }}</p>
                <p class="admin-stat__note">Everything stored in the database.</p>
            </div>

            <div class="admin-stat admin-stat--ok">
                <p class="admin-stat__label">Published</p>
                <p class="admin-stat__value">{{ $counts['published'] }}</p>
                <p class="admin-stat__note">
                    <a class="link-arrow" href="{{ route('projects.index') }}">
                        Visible on the public portfolio
                        <x-icon name="arrow-right" />
                    </a>
                </p>
            </div>

            <div class="admin-stat admin-stat--warn">
                <p class="admin-stat__label">Drafts</p>
                <p class="admin-stat__value">{{ $counts['draft'] }}</p>
                <p class="admin-stat__note">Never shown to visitors.</p>
            </div>

            <div class="admin-stat admin-stat--danger">
                <p class="admin-stat__label">Unpublished</p>
                <p class="admin-stat__value">{{ $counts['unpublished'] }}</p>
                <p class="admin-stat__note">Hidden again, kept in the database.</p>
            </div>

            <div class="admin-stat admin-stat--accent">
                <p class="admin-stat__label">Featured</p>
                <p class="admin-stat__value">{{ $counts['featured'] }}</p>
                <p class="admin-stat__note">Marked as showcase work.</p>
            </div>

            <div class="admin-stat">
                <p class="admin-stat__label">Demo placeholders</p>
                <p class="admin-stat__value">{{ $counts['placeholders'] }}</p>
                <p class="admin-stat__note">Labelled honestly on the public pages.</p>
            </div>
        </div>
    </section>

    <section class="admin-section" aria-label="Quick actions">
        <div class="admin-card">
            <div class="admin-card__head">
                <h2 class="admin-card__title">Quick actions</h2>
            </div>

            <div class="admin-card__body">
                <div class="chip-list">
                    <a class="btn btn--primary btn--sm" href="{{ route('admin.projects.create') }}">
                        <x-icon name="plus" />
                        Add a project
                    </a>

                    <a class="btn btn--ghost btn--sm" href="{{ route('admin.projects.index') }}">
                        <x-icon name="briefcase" />
                        Manage projects
                    </a>

                    <a class="btn btn--ghost btn--sm" href="{{ route('admin.profile.edit') }}">
                        <x-icon name="user" />
                        Edit profile settings
                    </a>

                    <a class="btn btn--ghost btn--sm" href="{{ route('home') }}" target="_blank" rel="noopener">
                        <x-icon name="external-link" />
                        View the portfolio
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="admin-section" aria-label="Recent activity">
        <div class="admin-card">
            <div class="admin-card__head">
                <h2 class="admin-card__title">Recently updated</h2>
                <a class="link-arrow" href="{{ route('admin.projects.index') }}">
                    All projects
                    <x-icon name="arrow-right" />
                </a>
            </div>

            @if ($recentlyUpdated->isEmpty())
                <div class="admin-card__body">
                    <div class="empty-state">
                        <h3>No projects yet</h3>
                        <p>Add your first project to see it here and on the public portfolio.</p>
                        <a class="btn btn--primary btn--sm" href="{{ route('admin.projects.create') }}">
                            <x-icon name="plus" />
                            Add a project
                        </a>
                    </div>
                </div>
            @else
                <ul class="admin-list">
                    @foreach ($recentlyUpdated as $project)
                        <li class="admin-list__item">
                            <div class="admin-list__main">
                                <p class="admin-list__title">
                                    <a href="{{ route('admin.projects.edit', $project) }}">{{ $project->title }}</a>

                                    @if ($project->isDemo())
                                        <span class="badge badge--demo">Demo placeholder</span>
                                    @endif
                                </p>
                                <p class="admin-list__meta">
                                    Updated {{ $project->updated_at->diffForHumans() }}
                                    · <span class="badge {{ $project->status->badgeModifier() }}">{{ $project->status->label() }}</span>
                                </p>
                            </div>

                            <div class="admin-table__actions">
                                <a class="btn btn--ghost btn--sm" href="{{ route('admin.projects.edit', $project) }}">Edit</a>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </section>
@endsection
