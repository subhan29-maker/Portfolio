@extends('layouts.app')

@section('title', 'Skills')
@section('meta_description', 'Skills and tools '.$profile['name'].' works with: HTML, CSS, JavaScript, responsive UI, PHP, Laravel, MySQL, Redis and Visual Studio Code.')

@section('content')
    <x-page-hero
        eyebrow="Skills"
        title="What I work with, grouped honestly"
        lead="Grouped by area rather than ranked by score. There are no percentages here — a number out of a hundred would tell you very little about whether I can finish your feature."
    />

    <section class="section">
        <div class="container">
            <div class="skill-grid skill-grid--wide">
                @foreach ($skillGroups as $index => $group)
                    <x-skill-panel :group="$group" :delay="$index * 80" :level="2" />
                @endforeach
            </div>
        </div>
    </section>

    <x-tech-ticker :technologies="$technologies" />

    <section class="section">
        <div class="container next-grid">
            <div>
                <x-section-heading
                    eyebrow="In practice"
                    title="How these fit together in a project"
                    lead="A typical request in a Laravel application passes through most of this list."
                />
            </div>

            <ol class="steps-list">
                <li class="steps-list__item" data-reveal="up">
                    <span class="steps-list__number" aria-hidden="true">01</span>
                    <div>
                        <h3 class="steps-list__title">Markup and styling</h3>
                        <p class="steps-list__text">Blade templates, semantic HTML and CSS built from design tokens, laid out with Grid and Flexbox.</p>
                    </div>
                </li>
                <li class="steps-list__item" data-reveal="up" data-reveal-delay="90">
                    <span class="steps-list__number" aria-hidden="true">02</span>
                    <div>
                        <h3 class="steps-list__title">Interaction</h3>
                        <p class="steps-list__text">A small amount of vanilla JavaScript where it earns its place: menus, reveals, live validation hints.</p>
                    </div>
                </li>
                <li class="steps-list__item" data-reveal="up" data-reveal-delay="180">
                    <span class="steps-list__number" aria-hidden="true">03</span>
                    <div>
                        <h3 class="steps-list__title">Application logic</h3>
                        <p class="steps-list__text">Laravel routes and controllers, form requests for validation, and Eloquent models for the domain.</p>
                    </div>
                </li>
                <li class="steps-list__item" data-reveal="up" data-reveal-delay="270">
                    <span class="steps-list__number" aria-hidden="true">04</span>
                    <div>
                        <h3 class="steps-list__title">Data</h3>
                        <p class="steps-list__text">MySQL tables and relationships, with Redis in front of the expensive reads when it helps.</p>
                    </div>
                </li>
            </ol>
        </div>
    </section>

    <x-cta
        title="Have something that needs building?"
        lead="Tell me what you have in mind and I'll tell you honestly whether it's a good fit."
    />
@endsection
