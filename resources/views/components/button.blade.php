@props([
    'href' => null,
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'external' => false,
])

@php
    $tag = filled($href) ? 'a' : 'button';
    $classes = 'btn btn--'.$variant.' btn--'.$size;
    $attributes = $attributes->merge(['class' => $classes]);
@endphp

<{{ $tag }}
    @if (filled($href)) href="{{ $href }}" @else type="button" @endif
    @if ($external) target="_blank" rel="noopener noreferrer" @endif
    {{ $attributes }}
>
    <span class="btn__label">{{ $slot }}</span>
    @if (filled($icon))
        <x-icon :name="$icon" />
    @endif
</{{ $tag }}>
