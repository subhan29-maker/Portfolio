@props(['variant' => 'default', 'icon' => null])

<span {{ $attributes->merge(['class' => 'badge badge--'.$variant]) }}>
    @if (filled($icon))
        <x-icon :name="$icon" class="icon--sm" />
    @endif
    {{ $slot }}
</span>
