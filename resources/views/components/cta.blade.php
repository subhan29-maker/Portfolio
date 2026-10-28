@props([
    'title' => 'Have a project in mind?',
    'lead' => "I'm happy to talk through an idea, a feature you need built, or a problem in an existing application.",
])

<section class="cta-band">
    <div class="container">
        <div class="cta-band__inner" data-reveal="up">
            <div class="cta-band__text">
                <h2 class="cta-band__title">{{ $title }}</h2>
                <p class="cta-band__lead">{{ $lead }}</p>
            </div>

            <div class="cta-band__actions">
                <a class="btn btn--primary" href="{{ route('contact') }}">
                    <span class="btn__label">Contact me</span>
                    <x-icon name="arrow-right" />
                </a>
                <a class="btn btn--ghost" href="{{ route('projects.index') }}">
                    <span class="btn__label">See my projects</span>
                </a>
            </div>
        </div>
    </div>
</section>
