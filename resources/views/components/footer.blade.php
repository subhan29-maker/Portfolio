@php
    $year = date('Y');
    $socials = $profile['socials'] ?? [];
@endphp

<footer class="site-footer">
    <div class="container">
        <div class="site-footer__top">
            <div class="site-footer__intro">
                <p class="site-footer__brand">{{ $profile['name'] }}</p>
                <p class="site-footer__summary">
                    {{ $profile['role'] }} working with HTML, CSS, JavaScript, PHP, Laravel, MySQL and Redis.
                </p>

                @if (filled($profile['email']))
                    <a class="site-footer__email" href="mailto:{{ $profile['email'] }}">{{ $profile['email'] }}</a>
                @endif
            </div>

            <nav class="site-footer__nav" aria-label="Footer">
                <h2 class="site-footer__heading">Pages</h2>
                <ul class="site-footer__list">
                    @foreach ($navigation as $item)
                        <li>
                            <a class="site-footer__link" href="{{ route($item['route']) }}">{{ $item['label'] }}</a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            <div class="site-footer__nav">
                <h2 class="site-footer__heading">Skills</h2>
                <ul class="site-footer__list">
                    @foreach (['Frontend development', 'Laravel applications', 'Database integration', 'Responsive UI'] as $item)
                        <li><a class="site-footer__link" href="{{ route('skills') }}">{{ $item }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div class="site-footer__nav">
                <h2 class="site-footer__heading">Elsewhere</h2>
                <ul class="site-footer__list">
                    @forelse ($socials as $social)
                        <li>
                            <a
                                class="site-footer__link"
                                href="{{ $social['url'] }}"
                                rel="noopener noreferrer"
                                target="_blank"
                            >
                                <x-icon :name="$social['icon']" class="icon--sm" />
                                {{ $social['label'] }}
                            </a>
                        </li>
                    @empty
                        <li>
                            <a class="site-footer__link" href="{{ route('contact') }}">
                                <x-icon name="mail" class="icon--sm" />
                                Get in touch
                            </a>
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="site-footer__bottom">
            <p class="site-footer__legal">© {{ $year }} {{ $profile['name'] }}. Built with Laravel and CSS.</p>
            <a class="site-footer__top-link" href="#page-top">
                Back to top
                <x-icon name="arrow-up" class="icon--sm" />
            </a>
        </div>
    </div>
</footer>
