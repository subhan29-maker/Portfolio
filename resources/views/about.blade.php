@extends('layouts.app')

@section('title', 'About')
@section('meta_description', 'About '.$profile['name'].': a full-stack developer with around two years of learning and roughly six months of practical experience building Laravel applications and responsive interfaces.')

@section('content')
    <x-page-hero
        eyebrow="About"
        title="I build web applications from the interface down to the database."
        lead="I'm {{ $profile['name'] }}, a full-stack developer. Around two years of learning and roughly six months of practical work have taken me from static pages to complete Laravel applications."
    />

    <section class="section">
        <div class="container about-grid">
            <div class="about-grid__content">
                <x-section-heading
                    eyebrow="Who I am"
                    title="A developer who likes the whole request"
                    :reveal="false"
                />

                <div class="prose" data-reveal="up">
                    <p>
                        I work with HTML, CSS, JavaScript, PHP and Laravel. What I enjoy most is the point where a
                        careful interface meets a well-structured backend: a form that validates properly, a query
                        that returns exactly what the page needs, and a layout that still reads well on a small phone.
                    </p>
                    <p>
                        Most of my time goes into frontend implementation and Laravel development. I build responsive
                        layouts with CSS Grid and Flexbox, keep markup semantic, and add JavaScript for the
                        interactions that genuinely need it rather than for everything.
                    </p>
                    <p>
                        I'm still early in my career and I'd rather say that plainly than inflate it. What I bring is a
                        solid hold on the fundamentals, a habit of reading documentation properly, and a real
                        willingness to keep learning.
                    </p>
                </div>
            </div>

            <div class="about-grid__aside">
                <figure class="about-portrait" data-reveal="right">
                    <x-image
                        :src="$profile['photo']"
                        :alt="$profile['photo_alt']"
                        fallback="images/placeholder.svg"
                    />
                </figure>

                <dl class="fact-list" data-reveal="right" data-reveal-delay="120">
                    <div class="fact-list__row">
                        <dt class="fact-list__term">Focus</dt>
                        <dd class="fact-list__value">Full-stack web development</dd>
                    </div>
                    <div class="fact-list__row">
                        <dt class="fact-list__term">Frontend</dt>
                        <dd class="fact-list__value">HTML, CSS, JavaScript, responsive UI</dd>
                    </div>
                    <div class="fact-list__row">
                        <dt class="fact-list__term">Backend</dt>
                        <dd class="fact-list__value">PHP, Laravel, MySQL, Redis</dd>
                    </div>
                    <div class="fact-list__row">
                        <dt class="fact-list__term">Editor</dt>
                        <dd class="fact-list__value">Visual Studio Code</dd>
                    </div>
                    @if (filled($profile['location']))
                        <div class="fact-list__row">
                            <dt class="fact-list__term">Based in</dt>
                            <dd class="fact-list__value">{{ $profile['location'] }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>
    </section>

    <section class="section section--muted">
        <div class="container">
            <x-section-heading
                eyebrow="My journey"
                title="Two years of learning, six months of building"
                lead="No employer history to pad this out — just the two stages that got me here."
            />

            <ol class="timeline">
                <li class="timeline__item" data-reveal="left">
                    <span class="timeline__marker" aria-hidden="true"></span>
                    <div class="timeline__body">
                        <p class="timeline__step">Learning</p>
                        <h3 class="timeline__title">Around two years of study and practice</h3>
                        <p class="timeline__text">
                            I started with HTML and CSS, moved on to JavaScript, and then into PHP and Laravel.
                            Along the way I learned what separates a page that merely looks right from one that
                            survives being changed: consistent naming, reusable components, and CSS built from a
                            small set of design tokens instead of scattered one-off values.
                        </p>
                    </div>
                </li>

                <li class="timeline__item" data-reveal="left" data-reveal-delay="120">
                    <span class="timeline__marker" aria-hidden="true"></span>
                    <div class="timeline__body">
                        <p class="timeline__step">Practical experience</p>
                        <h3 class="timeline__title">Around six months of hands-on development</h3>
                        <p class="timeline__text">
                            Lately I've been building rather than following tutorials: writing routes, controllers
                            and migrations, connecting forms to a database, debugging queries, and turning designs
                            into responsive interfaces that hold up across screen sizes.
                        </p>
                    </div>
                </li>
            </ol>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <x-section-heading
                eyebrow="How I work"
                title="Principles I try to code by"
                lead="Nothing revolutionary — just the habits that keep a codebase understandable six months later."
            />

            <ul class="feature-list">
                @php
                    $principles = [
                        ['icon' => 'code', 'title' => 'Readable over clever', 'text' => 'Code is read far more often than it is written. I favour clear names, small methods and predictable structure over shortcuts that only make sense to the person who wrote them.'],
                        ['icon' => 'layout', 'title' => 'Semantic, accessible markup', 'text' => 'Headings in order, labels tied to their inputs, meaningful alt text, and focus states a keyboard user can actually see. Accessibility is part of building the interface, not a later pass.'],
                        ['icon' => 'layers', 'title' => 'Small, reusable pieces', 'text' => 'Blade components for anything that repeats, design tokens instead of scattered colour values, and JavaScript split into focused modules rather than one long file.'],
                        ['icon' => 'shield', 'title' => 'Validate on the server', 'text' => 'Every form is validated where it counts, with sensible length limits, escaped output and CSRF protection on anything that changes state. Client-side help is a convenience, never the safeguard.'],
                        ['icon' => 'zap', 'title' => 'Boring, dependable choices', 'text' => 'I would rather use what the framework already provides than pull in another package, and rather solve the problem in front of me than guess at one that has not arrived yet.'],
                        ['icon' => 'target', 'title' => 'Finish the details', 'text' => 'Responsive behaviour, empty states, loading and error messages, and a contact form that actually saves the message — the unglamorous parts are usually what makes a project feel finished.'],
                    ];
                @endphp

                @foreach ($principles as $index => $principle)
                    <li class="feature-list__item" data-reveal="up" data-reveal-delay="{{ $index * 80 }}">
                        <span class="feature-list__icon" aria-hidden="true">
                            <x-icon :name="$principle['icon']" />
                        </span>
                        <div class="feature-list__body">
                            <h3 class="feature-list__title">{{ $principle['title'] }}</h3>
                            <p class="feature-list__text">{{ $principle['text'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    <section class="section section--muted">
        <div class="container next-grid">
            <div>
                <x-section-heading
                    eyebrow="Currently"
                    title="Where I'm heading next"
                    lead="The areas I'm actively working on rather than claiming to have mastered."
                />
            </div>

            <ul class="check-list" data-reveal="right">
                <li class="check-list__item">
                    <x-icon name="check" />
                    <span>Going deeper into Laravel: automated tests with PHPUnit, queues, scheduled tasks and caching strategy.</span>
                </li>
                <li class="check-list__item">
                    <x-icon name="check" />
                    <span>Sharpening database skills — query optimisation, indexing and schema design in MySQL.</span>
                </li>
                <li class="check-list__item">
                    <x-icon name="check" />
                    <span>Keeping the front end current: CSS custom properties, modern layout, and accessible interaction patterns.</span>
                </li>
                <li class="check-list__item">
                    <x-icon name="check" />
                    <span>Writing more code in public and finishing projects end to end instead of leaving them at prototype stage.</span>
                </li>
            </ul>
        </div>
    </section>

    <x-cta
        title="Want to know more?"
        lead="The skills page covers exactly what I work with, and the contact form goes straight to my inbox."
    />
@endsection
