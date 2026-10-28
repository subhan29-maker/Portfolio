@extends('layouts.app')

@section('meta_description', $profile['name'].' is a full-stack developer building responsive, database-driven web applications with HTML, CSS, JavaScript, PHP and Laravel.')

@section('content')
    {{--
        Decorative 3D stage: perspective floor grid, ambient orbs, orbit rings
        and a drifting wireframe. Pure presentation — every layer is aria-hidden
        and pointer-inert, and modules/depth.js only nudges their depth offsets.
    --}}
    <section class="hero hero--spatial" data-parallax-root>
        <div class="hero__stage" aria-hidden="true">
            <span class="hero__grid" data-depth="0.4"></span>
            <span class="hero__orb hero__orb--a" data-depth="1.6"></span>
            <span class="hero__orb hero__orb--b" data-depth="2.6"></span>
            <span class="hero__ring hero__ring--a" data-depth="3.0"></span>
            <span class="hero__ring hero__ring--b" data-depth="2.1"></span>
            <span class="hero__diamond" data-depth="2.4"><span></span></span>
            <span class="hero__mesh" data-depth="0.9"></span>
            <span class="hero__beam" data-depth="1.2"></span>
        </div>

        <div class="hero__glow" aria-hidden="true"></div>

        <div class="container hero__inner hero__inner--split">
            <div class="hero__content hero__content--asymmetric">
                <p class="hero__eyebrow hero-anim hero-anim--up" data-depth="0.7">{{ $profile['hero_label'] }}</p>

                <h1 class="hero__title" data-depth="1.2">
                    <span class="hero__name hero-anim hero-anim--left">{{ $profile['name'] }}</span>
                    <span class="hero__headline hero-anim hero-anim--up hero-anim--delay-3">{{ $profile['hero_headline'] }}</span>
                </h1>

                <p class="hero__lead hero-anim hero-anim--up hero-anim--delay-4" data-depth="0.9">{{ $profile['hero_lead'] }}</p>

                <div class="hero__cta-row" data-depth="0.6">
                    <div class="hero__actions">
                        <a class="btn btn--primary hero-anim hero-anim--up hero-anim--delay-5" href="{{ route('projects.index') }}">
                            <span class="btn__label">View projects</span>
                            <x-icon name="arrow-right" />
                        </a>
                        <a class="btn btn--ghost hero-anim hero-anim--up hero-anim--delay-6" href="{{ route('contact') }}">
                            <span class="btn__label">Contact me</span>
                        </a>
                    </div>

                    @if (filled($profile['availability']))
                        <div class="hero__status-pill hero-anim hero-anim--up hero-anim--delay-7">
                            <span class="status-dot" aria-hidden="true"></span>
                            {{ $profile['availability'] }}
                        </div>
                    @endif
                </div>

                @if (filled($technologies))
                    <ul class="chip-list hero__chiplist hero-anim hero-anim--up hero-anim--delay-8" aria-label="Core technologies">
                        @foreach ($technologies as $technology)
                            <li class="chip">{{ $technology }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="hero__aside hero__aside--offset" data-depth="1.8">
                <div class="hero__frame" data-tilt>
                    <figure class="profile-card profile-card--hero hero-anim hero-anim--scale hero-anim--delay-2">
                        <div class="profile-card__media">
                            <x-image
                                :src="$profile['photo']"
                                :alt="$profile['photo_alt']"
                                fallback="images/placeholder.svg"
                                :priority="true"
                                class="media__image--portrait"
                            />
                        </div>

                        <div class="profile-card__scanline" aria-hidden="true"></div>
                    </figure>

                    <div class="hero__orbit hero__orbit--1" aria-hidden="true"></div>
                    <div class="hero__orbit hero__orbit--2" aria-hidden="true"></div>
                    <div class="hero__hud" aria-hidden="true">
                        <span class="hero__hud-dot"></span>
                        <span class="hero__hud-line"></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <x-tech-ticker :technologies="$technologies" />

    <section class="section section--intro">
        <div class="container intro-grid">
            <div class="intro-grid__heading">
                <x-section-heading
                    eyebrow="What I do"
                    title="Complete web applications, from interface to database"
                    lead="I work across the whole request: the markup and styling a visitor sees, the JavaScript that makes it interactive, and the Laravel code that validates input and talks to the database."
                />
            </div>

            <ul class="intro-list">
                <li class="intro-list__item" data-reveal="left" data-reveal-delay="0">
                    <span class="intro-list__icon" aria-hidden="true"><x-icon name="layout" /></span>
                    <h3 class="intro-list__title">Frontend interfaces</h3>
                    <p class="intro-list__text">
                        Semantic HTML, modern CSS with Flexbox and Grid, and vanilla JavaScript for the
                        interactive parts — built mobile-first and checked at every breakpoint.
                    </p>
                </li>
                <li class="intro-list__item" data-reveal="left" data-reveal-delay="110">
                    <span class="intro-list__icon" aria-hidden="true"><x-icon name="server" /></span>
                    <h3 class="intro-list__title">Laravel backends</h3>
                    <p class="intro-list__text">
                        Routes, controllers, Blade views and Eloquent models, with server-side validation and
                        clear separation between application logic and presentation.
                    </p>
                </li>
                <li class="intro-list__item" data-reveal="left" data-reveal-delay="220">
                    <span class="intro-list__icon" aria-hidden="true"><x-icon name="database" /></span>
                    <h3 class="intro-list__title">Databases and caching</h3>
                    <p class="intro-list__text">
                        Relational schemas and queries in MySQL, and Redis for caching and lightweight
                        key-value data where a round trip to the database is unnecessary.
                    </p>
                </li>
            </ul>
        </div>
    </section>

    <section class="section section--muted">
        <div class="container">
            <div class="section-header-row">
                <x-section-heading
                    eyebrow="Skills"
                    title="The tools I build with"
                    lead="Grouped by area rather than by score — here is what I actually work with day to day."
                />
                <a class="link-arrow" href="{{ route('skills') }}" data-reveal="right">
                    All skills
                    <x-icon name="arrow-right" class="icon--sm" />
                </a>
            </div>

            <div class="skill-grid">
                @foreach ($skillGroups as $index => $group)
                    <x-skill-panel :group="$group" :delay="$index * 90" />
                @endforeach
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="section-header-row">
                <x-section-heading
                    eyebrow="Selected work"
                    title="Projects I'm building"
                    lead="A small, honest selection. Each project page explains the problem, the features and the technologies involved."
                />
                <a class="link-arrow" href="{{ route('projects.index') }}" data-reveal="right">
                    All projects
                    <x-icon name="arrow-right" class="icon--sm" />
                </a>
            </div>

            @if ($featuredProjects->isEmpty())
                <div class="empty-state" data-reveal="up">
                    <x-icon name="layers" class="empty-state__icon" />
                    <h3 class="empty-state__title">Projects are on the way</h3>
                    <p class="empty-state__text">
                        I'm putting the finishing touches on write-ups for the applications I have been
                        building. In the meantime, the skills page covers the technologies I work with.
                    </p>
                    <a class="btn btn--ghost btn--sm" href="{{ route('skills') }}">
                        <span class="btn__label">View my skills</span>
                    </a>
                </div>
            @else
                <div class="project-grid">
                    @foreach ($featuredProjects as $project)
                        <x-project-card :project="$project" :delay="$loop->index * 90" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <x-cta />
@endsection
