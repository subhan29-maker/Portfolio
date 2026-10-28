@extends('layouts.app')

@section('title', 'Projects')
@section('meta_description', 'Web application projects by '.$profile['name'].', built with Laravel, PHP, MySQL, Redis and a responsive front end.')

@section('content')
    <x-page-hero
        eyebrow="Projects"
        title="Applications I have been building"
        lead="One collection, each with the problem it addresses, the features it includes and the technologies behind it."
    />

    <section class="section">
        <div class="container">
            @if ($showingDemoNotice)
                <div class="notice" data-reveal="up" role="note">
                    <x-icon name="info" class="notice__icon" />
                    <div class="notice__body">
                        <h2 class="notice__title">These entries are placeholders</h2>
                        <p class="notice__text">
                            The projects below are clearly labelled examples that ship with this site so the layout can
                            be seen with real content. They are not client work. Each one will be replaced with an
                            actual project — and I'd rather show an honest placeholder than an invented case study.
                        </p>
                    </div>
                </div>
            @endif

            @if ($projects->isEmpty())
                <div class="empty-state" data-reveal="up">
                    <x-icon name="layers" class="empty-state__icon" />
                    <h2 class="empty-state__title">No projects published yet</h2>
                    <p class="empty-state__text">
                        I'm finishing write-ups for the applications I have been building. The skills page shows what
                        I'm working with in the meantime, and you're welcome to get in touch directly.
                    </p>
                    <div class="empty-state__actions">
                        <a class="btn btn--primary btn--sm" href="{{ route('contact') }}">
                            <span class="btn__label">Contact me</span>
                            <x-icon name="arrow-right" />
                        </a>
                        <a class="btn btn--ghost btn--sm" href="{{ route('skills') }}">
                            <span class="btn__label">View skills</span>
                        </a>
                    </div>
                </div>
            @else
                <div class="project-grid project-grid--catalogue">
                    @foreach ($projects as $project)
                        <x-project-card :project="$project" :delay="($loop->index % 3) * 90" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <x-cta />
@endsection
