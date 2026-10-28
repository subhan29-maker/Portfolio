@props([
    'eyebrow' => null,
    'title' => null,
    'lead' => null,
])

<header class="page-hero">
    <div class="container page-hero__inner">
        @if (filled($eyebrow))
            <p class="page-hero__eyebrow" data-reveal="up">{{ $eyebrow }}</p>
        @endif

        @if (filled($title))
            <h1 class="page-hero__title" data-reveal="up" data-reveal-delay="70">{{ $title }}</h1>
        @endif

        @if (filled($lead))
            <p class="page-hero__lead" data-reveal="up" data-reveal-delay="140">{{ $lead }}</p>
        @endif

        {{ $slot }}
    </div>
</header>
