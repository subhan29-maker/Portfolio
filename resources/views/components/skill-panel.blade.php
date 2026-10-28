@props(['group' => [], 'delay' => 0, 'level' => 3])

<article class="skill-panel" data-reveal="up" data-reveal-delay="{{ $delay }}" data-glow>
    <header class="skill-panel__header">
        <span class="skill-panel__icon" aria-hidden="true">
            <x-icon :name="$group['icon'] ?? 'sparkle'" />
        </span>
        <div>
            <h{{ $level }} class="skill-panel__title">{{ $group['title'] }}</h{{ $level }}>
            <p class="skill-panel__summary">{{ $group['summary'] ?? '' }}</p>
        </div>
    </header>

    <ul class="skill-list">
        @foreach ($group['items'] ?? [] as $item)
            <li class="skill-list__item">
                <span class="skill-list__name">{{ $item['name'] }}</span>
                <span class="skill-list__description">{{ $item['description'] ?? '' }}</span>
            </li>
        @endforeach
    </ul>
</article>
