@php
    $brandInitial = mb_substr($profile['name'], 0, 1);
@endphp

<header class="site-header" data-header>
    <div class="container site-header__inner">
        <a class="brand" href="{{ route('home') }}" aria-label="{{ $profile['name'] }} — home">
            <span class="brand__mark" aria-hidden="true">{{ $brandInitial }}</span>
            <span class="brand__text">
                <span class="brand__name">{{ $profile['name'] }}</span>
                <span class="brand__role">{{ $profile['role'] }}</span>
            </span>
        </a>

        <button
            class="nav-toggle"
            type="button"
            data-nav-toggle
            aria-expanded="false"
            aria-controls="primary-navigation"
        >
            <x-icon name="menu" class="nav-toggle__icon nav-toggle__icon--open" />
            <x-icon name="close" class="nav-toggle__icon nav-toggle__icon--close" />
            <span class="sr-only">Toggle navigation menu</span>
        </button>

        <nav class="site-nav" id="primary-navigation" data-nav aria-label="Primary">
            <ul class="site-nav__list">
                @foreach ($navigation as $item)
                    @php($isActive = request()->routeIs($item['route']))
                    <li class="site-nav__item">
                        <a
                            class="site-nav__link {{ $isActive ? 'is-active' : '' }}"
                            href="{{ route($item['route']) }}"
                            @if ($isActive) aria-current="page" @endif
                        >{{ $item['label'] }}</a>
                    </li>
                @endforeach
            </ul>

            <div class="site-nav__footer">
                @if (filled($profile['email']))
                    <a class="site-nav__contact" href="mailto:{{ $profile['email'] }}">{{ $profile['email'] }}</a>
                @endif
                <a class="btn btn--primary btn--sm" href="{{ route('contact') }}">
                    Start a conversation
                    <x-icon name="arrow-right" />
                </a>
            </div>
        </nav>
    </div>
</header>
