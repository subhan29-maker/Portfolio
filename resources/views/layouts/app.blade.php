<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="no-js">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0a0f1c">
    <meta name="color-scheme" content="dark">

    <title>@hasSection('title')@yield('title') — {{ $profile['name'] }}@else{{ $profile['name'] }} — {{ $profile['role'] }}@endif</title>
    <meta name="description" content="@yield('meta_description', $profile['name'].' is a full-stack developer building responsive web applications with HTML, CSS, JavaScript, PHP, Laravel, MySQL and Redis.')">
    <meta name="author" content="{{ $profile['name'] }}">
    <link rel="canonical" href="{{ url()->current() }}">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $profile['name'] }}">
    <meta property="og:title" content="@hasSection('title')@yield('title') — {{ $profile['name'] }}@else{{ $profile['name'] }} — {{ $profile['role'] }}@endif">
    <meta property="og:description" content="@yield('meta_description', $profile['name'].' is a full-stack developer building responsive web applications with Laravel.')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="twitter:card" content="summary">

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    <meta property="og:image" content="{{ asset('images/placeholder.svg') }}">
    <meta name="twitter:image" content="{{ asset('images/placeholder.svg') }}">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.seo-json-ld')

    {{--
        Marks the document as script-capable before first paint (so reveal
        animations can hide their elements without a flash) and guarantees
        content becomes visible even if the JavaScript bundle never loads.
    --}}
    <script>
        (function () {
            var root = document.documentElement;
            root.classList.remove('no-js');
            root.classList.add('js');
            window.setTimeout(function () {
                if (root.dataset.revealReady !== 'true') {
                    root.classList.add('reveal-fallback');
                }
            }, 2500);
        })();
    </script>
</head>
<body class="site">
    <a class="skip-link" href="#main">Skip to main content</a>

    <div class="scroll-sentinel" id="page-top" data-scroll-sentinel aria-hidden="true"></div>

    <x-nav />

    <main id="main" class="site-main">
        @yield('content')
    </main>

    <x-footer />
</body>
</html>
