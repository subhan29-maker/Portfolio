<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#080d18">
    <meta name="color-scheme" content="dark">

    <title>@yield('title', 'Dashboard') — {{ $profile['name'] }}</title>

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    @fonts
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
</head>
<body>
    <a class="skip-link" href="#main">Skip to main content</a>

    <div class="admin-shell" data-admin-shell>
        <aside class="admin-sidebar" id="admin-sidebar">
            <div class="admin-brand">
                <span class="admin-brand__mark" aria-hidden="true">{{ str($profile['name'])->substr(0, 1)->upper() }}</span>
                <span>
                    <span class="admin-brand__name">{{ $profile['name'] }}</span>
                    <span class="admin-brand__meta">Dashboard</span>
                </span>
            </div>

            <nav class="admin-nav" aria-label="Dashboard">
                <x-admin.nav-link :href="route('admin.dashboard')" icon="grid" :active="request()->routeIs('admin.dashboard')">
                    Dashboard
                </x-admin.nav-link>

                <span class="admin-nav__label">Portfolio</span>

                <x-admin.nav-link :href="route('admin.projects.index')" icon="briefcase" :active="request()->routeIs('admin.projects.*')">
                    Projects
                </x-admin.nav-link>

                <x-admin.nav-link :href="route('admin.profile.edit')" icon="user" :active="request()->routeIs('admin.profile.*')">
                    Profile &amp; settings
                </x-admin.nav-link>

                <span class="admin-nav__label">Public site</span>

                <x-admin.nav-link :href="route('home')" icon="external-link">
                    View portfolio
                </x-admin.nav-link>
            </nav>

            <div class="admin-sidebar__foot">
                <div class="admin-sidebar__who">
                    <span class="admin-sidebar__avatar" aria-hidden="true">{{ str(auth()->user()->name)->substr(0, 1)->upper() }}</span>
                    <span class="admin-sidebar__email" title="{{ auth()->user()->email }}">{{ auth()->user()->email }}</span>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn--ghost btn--sm" type="submit">Sign out</button>
                </form>
            </div>
        </aside>

        <div class="admin-main">
            <header class="admin-topbar">
                <button
                    class="btn btn--ghost btn--sm admin-topbar__toggle"
                    type="button"
                    data-nav-toggle
                    aria-controls="admin-sidebar"
                    aria-expanded="false"
                >
                    <x-icon name="menu" />
                    <span class="sr-only">Menu</span>
                </button>

                <h1 class="admin-topbar__title">@yield('title', 'Dashboard')</h1>

                <div class="admin-topbar__actions">
                    @yield('actions')
                </div>
            </header>

            <main id="main" class="admin-content">
                <x-admin.flash />

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
