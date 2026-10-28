@extends('layouts.app')

@section('title', 'Page not found')

@section('content')
    <section class="section error-page">
        <div class="container error-page__inner">
            <p class="error-page__code" aria-hidden="true">404</p>
            <h1 class="error-page__title">That page doesn't exist</h1>
            <p class="error-page__text">
                The link may be out of date, or the address may have a typo in it.
                Everything else is still where you left it.
            </p>

            <div class="error-page__actions">
                <a class="btn btn--primary" href="{{ route('home') }}">
                    <span class="btn__label">Back to home</span>
                    <x-icon name="arrow-right" />
                </a>
                <a class="btn btn--ghost" href="{{ route('projects.index') }}">
                    <span class="btn__label">Browse projects</span>
                </a>
            </div>
        </div>
    </section>
@endsection
