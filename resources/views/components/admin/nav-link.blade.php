@props([
    'href',
    'icon' => null,
    'active' => false,
])

<a href="{{ $href }}" {{ $attributes->class(['admin-nav__link' => true, 'is-active' => $active]) }}>
    @if (filled($icon))
        <x-icon :name="$icon" />
    @endif
    <span>{{ $slot }}</span>
</a>
