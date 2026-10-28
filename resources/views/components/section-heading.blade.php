@props([
    'eyebrow' => null,
    'title' => null,
    'lead' => null,
    'align' => 'start',
    'level' => 2,
    'reveal' => true,
])

<div {{ $attributes->merge(['class' => 'section-heading section-heading--'.$align]) }}>
    @if (filled($eyebrow))
        <p class="section-heading__eyebrow" @if ($reveal) data-reveal="up" @endif>{{ $eyebrow }}</p>
    @endif

    @if (filled($title))
        <h{{ $level }} class="section-heading__title" @if ($reveal) data-reveal="up" data-reveal-delay="80" @endif>{{ $title }}</h{{ $level }}>
    @endif

    @if (filled($lead))
        <p class="section-heading__lead" @if ($reveal) data-reveal="up" data-reveal-delay="140" @endif>{{ $lead }}</p>
    @endif

    {{ $slot }}
</div>
