@extends('layouts.admin')

@section('title', $project->title)

@section('actions')
    <a class="btn btn--ghost btn--sm" href="{{ route('admin.projects.index') }}">Back to projects</a>

    <a class="btn btn--primary btn--sm" href="{{ route('admin.projects.edit', $project) }}">
        <x-icon name="code" />
        Edit
    </a>
@endsection

@section('content')
    <section class="admin-section">
        <div class="admin-columns">
            <div class="admin-column">
                <article class="admin-card">
                    <div class="admin-card__head">
                        <h2 class="admin-card__title">{{ $project->title }}</h2>

                        <span class="badge {{ $project->status->badgeModifier() }}">{{ $project->status->label() }}</span>
                    </div>

                    <div class="admin-card__body">
                        <dl class="admin-detail-list">
                            <div>
                                <dt>Public URL</dt>
                                <dd>
                                    @if ($project->isPublished())
                                        <a class="link-arrow" href="{{ route('projects.show', $project->slug) }}" target="_blank" rel="noopener">
                                            /projects/{{ $project->slug }}
                                            <x-icon name="external-link" />
                                        </a>
                                    @else
                                        <code>/projects/{{ $project->slug }}</code>
                                        <span class="admin-table__meta">(hidden until published)</span>
                                    @endif
                                </dd>
                            </div>

                            <div>
                                <dt>Summary</dt>
                                <dd>{{ $project->summary ?: '—' }}</dd>
                            </div>

                            <div>
                                <dt>Category</dt>
                                <dd>{{ $project->category ?: '—' }}</dd>
                            </div>

                            <div>
                                <dt>Technologies</dt>
                                <dd>
                                    @if ($project->technologies)
                                        <span class="tag-list">
                                            @foreach ((array) $project->technologies as $technology)
                                                <span class="tag">{{ $technology }}</span>
                                            @endforeach
                                        </span>
                                    @else
                                        —
                                    @endif
                                </dd>
                            </div>

                            <div>
                                <dt>Live demo</dt>
                                <dd>
                                    @if (filled($project->live_demo_url))
                                        <a class="link-arrow" href="{{ $project->live_demo_url }}" target="_blank" rel="noopener">
                                            {{ $project->live_demo_url }}
                                            <x-icon name="external-link" />
                                        </a>
                                    @else
                                        —
                                    @endif
                                </dd>
                            </div>

                            <div>
                                <dt>Repository</dt>
                                <dd>
                                    @if (filled($project->repository_url))
                                        <a class="link-arrow" href="{{ $project->repository_url }}" target="_blank" rel="noopener">
                                            {{ $project->repository_url }}
                                            <x-icon name="external-link" />
                                        </a>
                                    @else
                                        —
                                    @endif
                                </dd>
                            </div>

                            <div>
                                <dt>Display position</dt>
                                <dd>{{ $project->sort_order }}</dd>
                            </div>

                            <div>
                                <dt>First published</dt>
                                <dd>{{ $project->published_at?->format('d M Y, H:i') ?? 'Never' }}</dd>
                            </div>

                            <div>
                                <dt>Last updated</dt>
                                <dd>{{ $project->updated_at->format('d M Y, H:i') }}</dd>
                            </div>
                        </dl>
                    </div>
                </article>

                @if (filled($project->description) || $project->features || $project->highlights)
                    <article class="admin-card">
                        <div class="admin-card__head">
                            <h2 class="admin-card__title">Public page content</h2>
                        </div>

                        <div class="admin-card__body">
                            @if (filled($project->problem))
                                <h3 class="admin-detail-heading">Problem it solves</h3>
                                <div class="prose"><p>{{ $project->problem }}</p></div>
                            @endif

                            @if (filled($project->purpose))
                                <h3 class="admin-detail-heading">Why it was built</h3>
                                <div class="prose"><p>{{ $project->purpose }}</p></div>
                            @endif

                            @if ($project->features)
                                <h3 class="admin-detail-heading">Features</h3>
                                <ul class="admin-detail-list-bullets">
                                    @foreach ((array) $project->features as $feature)
                                        <li>{{ $feature }}</li>
                                    @endforeach
                                </ul>
                            @endif

                            @if ($project->highlights)
                                <h3 class="admin-detail-heading">Highlights</h3>
                                <ul class="admin-detail-list-bullets">
                                    @foreach ((array) $project->highlights as $highlight)
                                        <li>{{ $highlight }}</li>
                                    @endforeach
                                </ul>
                            @endif

                            @if (filled($project->contribution))
                                <h3 class="admin-detail-heading">Your contribution</h3>
                                <div class="prose"><p>{{ $project->contribution }}</p></div>
                            @endif
                        </div>
                    </article>
                @endif
            </div>

            <div class="admin-column admin-column--narrow">
                <article class="admin-card">
                    <div class="admin-card__head">
                        <h2 class="admin-card__title">Cover</h2>
                    </div>

                    <div class="admin-card__body">
                        @if ($project->coverUrl(false))
                            <img class="admin-media__preview" src="{{ $project->coverUrl(false) }}" alt="Cover for {{ $project->title }}">
                        @else
                            <p class="admin-media__caption">No cover image.</p>
                        @endif
                    </div>
                </article>

                <article class="admin-card">
                    <div class="admin-card__head">
                        <h2 class="admin-card__title">Gallery</h2>
                    </div>

                    <div class="admin-card__body">
                        @if ($project->galleryUrls(false) === [])
                            <p class="admin-media__caption">No gallery images.</p>
                        @else
                            <div class="admin-media__grid">
                                @foreach ($project->galleryUrls(false) as $index => $src)
                                    <img class="admin-media__preview" src="{{ $src }}" alt="Gallery image {{ $index + 1 }} for {{ $project->title }}">
                                @endforeach
                            </div>
                        @endif
                    </div>
                </article>
            </div>
        </div>
    </section>
@endsection
