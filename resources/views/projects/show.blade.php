@extends('layouts.app')

@section('title', $project->title)
@section('meta_description', \Illuminate\Support\Str::limit($project->summary, 150))

@section('content')
    <article class="project-detail">
        <header class="project-detail__header">
            <div class="container">
                <a class="back-link" href="{{ route('projects.index') }}">
                    <x-icon name="arrow-left" class="icon--sm" />
                    All projects
                </a>

                <div class="project-detail__heading">
                    <div>
                        <p class="page-hero__eyebrow" data-reveal="up">Project</p>
                        <h1 class="project-detail__title" data-reveal="up" data-reveal-delay="70">{{ $project->title }}</h1>
                        <p class="project-detail__tagline" data-reveal="up" data-reveal-delay="130">{{ $project->tagline }}</p>
                    </div>

                    @if ($project->isDemo)
                        <x-badge variant="demo" icon="info">Demo placeholder</x-badge>
                    @endif
                </div>

                @if (filled($project->technologies))
                    <ul class="tag-list tag-list--lg" data-reveal="up" data-reveal-delay="180" aria-label="Technologies used">
                        @foreach ($project->technologies as $technology)
                            <li class="tag">{{ $technology }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </header>

        <div class="container">
            <figure class="project-detail__cover" data-reveal="up">
                <x-image
                    :src="$project->cover"
                    :alt="$project->title.' — main project screenshot'"
                    fallback="images/placeholder.svg"
                    :priority="true"
                />
            </figure>
        </div>

        <div class="container project-detail__layout">
            <div class="project-detail__main">
                @if ($project->isDemo)
                    <div class="notice notice--compact" role="note">
                        <x-icon name="info" class="notice__icon" />
                        <div class="notice__body">
                            <h2 class="notice__title">Placeholder entry</h2>
                            <p class="notice__text">
                                This page is example content shipped with the site so the project layout can be seen
                                with realistic information. It does not describe client work.
                            </p>
                        </div>
                    </div>
                @endif

                @if (filled($project->overview))
                    <section class="project-block" data-reveal="up">
                        <h2 class="project-block__title">Overview</h2>
                        <div class="prose"><p>{{ $project->overview }}</p></div>
                    </section>
                @endif

                @if (filled($project->problem) || filled($project->purpose))
                    <section class="project-block" data-reveal="up">
                        <h2 class="project-block__title">Purpose and problem</h2>
                        <div class="prose">
                            @if (filled($project->problem))
                                <p><strong>The problem:</strong> {{ $project->problem }}</p>
                            @endif
                            @if (filled($project->purpose))
                                <p><strong>The purpose:</strong> {{ $project->purpose }}</p>
                            @endif
                        </div>
                    </section>
                @endif

                @if (filled($project->features))
                    <section class="project-block" data-reveal="up">
                        <h2 class="project-block__title">Key features</h2>
                        <ul class="check-list">
                            @foreach ($project->features as $feature)
                                <li class="check-list__item">
                                    <x-icon name="check" />
                                    <span>{{ $feature }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                @if (filled($project->highlights))
                    <section class="project-block" data-reveal="up">
                        <h2 class="project-block__title">Technical highlights</h2>
                        <ul class="check-list">
                            @foreach ($project->highlights as $highlight)
                                <li class="check-list__item">
                                    <x-icon name="zap" />
                                    <span>{{ $highlight }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                @if ($project->gallery !== [])
                    <section class="project-block" data-reveal="up">
                        <h2 class="project-block__title">Screenshots</h2>
                        <div class="gallery">
                            @foreach ($project->gallery as $index => $shot)
                                <figure class="gallery__item">
                                    <x-image
                                        :src="$shot['src']"
                                        :alt="$shot['alt'] ?? $project->title.' — screenshot '.($index + 2)"
                                        fallback="images/placeholder.svg"
                                    />
                                </figure>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if (filled($project->contribution))
                    <section class="project-block" data-reveal="up">
                        <h2 class="project-block__title">My contribution</h2>
                        <div class="prose"><p>{{ $project->contribution }}</p></div>
                    </section>
                @endif
            </div>

            <aside class="project-detail__aside">
                <div class="detail-card" data-reveal="right">
                    <h2 class="detail-card__title">Project details</h2>

                    <dl class="fact-list">
                        @if (filled($project->role))
                            <div class="fact-list__row">
                                <dt class="fact-list__term">Role</dt>
                                <dd class="fact-list__value">{{ $project->role }}</dd>
                            </div>
                        @endif

                        @if (filled($project->year))
                            <div class="fact-list__row">
                                <dt class="fact-list__term">Year</dt>
                                <dd class="fact-list__value">{{ $project->year }}</dd>
                            </div>
                        @endif

                        <div class="fact-list__row">
                            <dt class="fact-list__term">Technologies</dt>
                            <dd class="fact-list__value">
                                {{ $project->technologies ? implode(', ', $project->technologies) : 'Not listed' }}
                            </dd>
                        </div>
                    </dl>

                    @if ($project->hasExternalLinks())
                        <div class="detail-card__actions">
                            @if (filled($project->demoUrl))
                                <a class="btn btn--primary btn--sm" href="{{ $project->demoUrl }}" target="_blank" rel="noopener noreferrer">
                                    <span class="btn__label">Live demo</span>
                                    <x-icon name="external-link" />
                                </a>
                            @endif

                            @if (filled($project->repoUrl))
                                <a class="btn btn--ghost btn--sm" href="{{ $project->repoUrl }}" target="_blank" rel="noopener noreferrer">
                                    <span class="btn__label">Source code</span>
                                    <x-icon name="github" />
                                </a>
                            @endif
                        </div>
                    @else
                        <p class="detail-card__note">
                            The live demo and repository links will appear here once they are available.
                        </p>
                    @endif
                </div>
            </aside>
        </div>

        @if ($relatedProjects->isNotEmpty())
            <section class="section section--muted">
                <div class="container">
                    <x-section-heading
                        eyebrow="Related"
                        title="Other projects"
                        lead="Projects that share technologies with this one."
                    />

                    <div class="project-grid">
                        @foreach ($relatedProjects as $related)
                            <x-project-card :project="$related" :delay="$loop->index * 90" />
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </article>

    <x-cta />
@endsection
