@props(['project', 'delay' => 0])

<article class="project-card project-card--featured" data-reveal="up" data-reveal-delay="{{ $delay }}">
    {{--
        The tilt transform lives on this inner layer (not the article) so it
        never collides with the reveal animation's transform. Without
        JavaScript the wrapper is just a plain flex column — identical layout.
    --}}
    <div class="project-card__depth project-card__depth--spatial" data-tilt>
        <a class="project-card__media" href="{{ route('projects.show', $project->slug) }}" tabindex="-1" aria-hidden="true">
            <x-image
                :src="$project->cover"
                :alt="$project->title.' — project screenshot'"
                fallback="images/placeholder.svg"
            />
        </a>

        <div class="project-card__body">
            <div class="project-card__header">
                <div class="project-card__meta">
                    @if ($project->isDemo)
                        <x-badge variant="demo" icon="info">Demo placeholder</x-badge>
                    @elseif (filled($project->year))
                        <x-badge variant="muted">{{ $project->year }}</x-badge>
                    @endif
                </div>
                <span class="project-card__index" aria-hidden="true">0{{ $loop->iteration ?? 1 }}</span>
            </div>

            <h3 class="project-card__title">
                <a href="{{ route('projects.show', $project->slug) }}">{{ $project->title }}</a>
            </h3>

            <p class="project-card__summary">{{ $project->summary }}</p>

            @if (filled($project->technologies))
                <ul class="tag-list" aria-label="Technologies used">
                    @foreach ($project->technologies as $technology)
                        <li class="tag">{{ $technology }}</li>
                    @endforeach
                </ul>
            @endif

            <div class="project-card__actions">
                <a class="link-arrow" href="{{ route('projects.show', $project->slug) }}">
                    View details
                    <x-icon name="arrow-right" class="icon--sm" />
                </a>

                @if (filled($project->demoUrl))
                    <a class="link-muted" href="{{ $project->demoUrl }}" target="_blank" rel="noopener noreferrer">
                        Live demo
                        <x-icon name="external-link" class="icon--sm" />
                    </a>
                @endif

                @if (filled($project->repoUrl))
                    <a class="link-muted" href="{{ $project->repoUrl }}" target="_blank" rel="noopener noreferrer">
                        Code
                        <x-icon name="github" class="icon--sm" />
                    </a>
                @endif
            </div>
        </div>

        {{-- Lifted above the card's other 3D layers so it always paints on top. --}}
        <span class="project-card__glare" aria-hidden="true"></span>
        <span class="project-card__grid" aria-hidden="true"></span>
    </div>
</article>
