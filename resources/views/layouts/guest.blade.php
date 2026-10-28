<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#080d18">
    <meta name="color-scheme" content="dark">

    <title>@yield('title') — {{ $profile['name'] }}</title>

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    @fonts
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
</head>
<body>
    <main class="admin-guest">
        <div class="admin-guest__card">
            @yield('content')
        </div>
    </main>
</body>
</html>
