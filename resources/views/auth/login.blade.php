@extends('layouts.guest')

@section('title', 'Sign in')

@section('content')
    <div class="admin-guest__head">
        <div class="admin-brand" style="justify-content: center;">
            <span class="admin-brand__mark" aria-hidden="true">{{ str($profile['name'])->substr(0, 1)->upper() }}</span>
            <span>
                <span class="admin-brand__name">{{ $profile['name'] }}</span>
                <span class="admin-brand__meta">Dashboard</span>
            </span>
        </div>

        <h1 class="admin-guest__title">Sign in</h1>
        <p class="admin-guest__lead">Administrator access to the portfolio.</p>
    </div>

    <form method="POST" action="{{ route('login.store') }}">
        @csrf

        <x-form.input
            name="email"
            label="Email address"
            type="email"
            :value="old('email')"
            autocomplete="username"
            required
        />

        <x-form.input
            name="password"
            label="Password"
            type="password"
            autocomplete="current-password"
            required
        />

        <x-form.checkbox
            name="remember"
            label="Keep me signed in"
            hint="Your session stays open for up to 120 days on this device."
            :checked="old('remember') !== null"
        />

        <button class="btn btn--primary" type="submit">
            <x-icon name="shield" />
            Sign in
        </button>
    </form>

    <p class="admin-guest__foot">
        <a class="link-muted" href="{{ route('home') }}">Back to the portfolio</a>
    </p>
@endsection
