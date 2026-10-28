@props(['technologies' => []])

@php
    $items = array_values($technologies);
    $accessibleList = implode(', ', $items);
@endphp

<section class="ticker-section" aria-label="Technologies I work with">
    <div class="container">
        <p class="ticker-section__label" data-reveal="up">Technology stack</p>
    </div>

    {{-- The moving strip is decorative duplication; screen readers get the static list below. --}}
    <div class="ticker" aria-hidden="true">
        <div class="ticker__track">
            @for ($copy = 0; $copy < 2; $copy++)
                <div class="ticker__group">
                    @foreach ($items as $technology)
                        <span class="ticker__item">
                            <span class="ticker__dot" aria-hidden="true"></span>
                            {{ $technology }}
                        </span>
                    @endforeach
                </div>
            @endfor
        </div>
    </div>

    <p class="sr-only">{{ $accessibleList }}</p>
</section>
