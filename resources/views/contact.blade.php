@extends('layouts.app')

@section('title', 'Contact')
@section('meta_description', 'Get in touch with '.$profile['name'].' about a web application, a Laravel feature or a frontend build.')

@section('content')
    <x-page-hero
        eyebrow="Contact"
        title="Tell me what you're building"
        lead="Share a little about the project and I'll reply with an honest answer about whether I'm the right fit."
    />

    <section class="section">
        <div class="container contact-grid">
            <div class="contact-aside">
                <div class="contact-card" data-reveal="left">
                    <h2 class="contact-card__title">How to reach me</h2>

                    <ul class="contact-details">
                        @if (filled($profile['email']))
                            <li class="contact-details__item">
                                <x-icon name="mail" />
                                <span>
                                    <span class="contact-details__label">Email</span>
                                    <a class="contact-details__value" href="mailto:{{ $profile['email'] }}">{{ $profile['email'] }}</a>
                                </span>
                            </li>
                        @endif

                        @if (filled($profile['phone']))
                            <li class="contact-details__item">
                                <x-icon name="phone" />
                                <span>
                                    <span class="contact-details__label">Phone</span>
                                    <a class="contact-details__value" href="tel:{{ preg_replace('/[^0-9+]/', '', $profile['phone']) }}">{{ $profile['phone'] }}</a>
                                </span>
                            </li>
                        @endif

                        @if (filled($profile['location']))
                            <li class="contact-details__item">
                                <x-icon name="map-pin" />
                                <span>
                                    <span class="contact-details__label">Location</span>
                                    <span class="contact-details__value">{{ $profile['location'] }}</span>
                                </span>
                            </li>
                        @endif

                        <li class="contact-details__item">
                            <x-icon name="clock" />
                            <span>
                                <span class="contact-details__label">Response time</span>
                                <span class="contact-details__value">{{ config('portfolio.contact.response_note') }}</span>
                            </span>
                        </li>
                    </ul>

                    @if (filled($profile['socials']))
                        <div class="contact-card__socials">
                            <h3 class="contact-card__subtitle">Elsewhere</h3>
                            <ul class="social-list">
                                @foreach ($profile['socials'] as $social)
                                    <li>
                                        <a class="social-list__link" href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer">
                                            <x-icon :name="$social['icon']" />
                                            {{ $social['label'] }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                <div class="contact-card contact-card--muted" data-reveal="left" data-reveal-delay="120">
                    <h2 class="contact-card__title">Helpful to include</h2>
                    <ul class="check-list check-list--sm">
                        <li class="check-list__item"><x-icon name="check" /><span>What the application needs to do.</span></li>
                        <li class="check-list__item"><x-icon name="check" /><span>Whether a design or brand already exists.</span></li>
                        <li class="check-list__item"><x-icon name="check" /><span>Any deadline you're working towards.</span></li>
                        <li class="check-list__item"><x-icon name="check" /><span>Your rough budget range, if you have one.</span></li>
                    </ul>
                </div>
            </div>

            <div class="contact-form-wrap" data-reveal="right">
                @if (session('contact_status'))
                    <div class="alert alert--success" role="status">
                        <x-icon name="check" class="alert__icon" />
                        <p>{{ session('contact_status') }}</p>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert--error" role="alert">
                        <x-icon name="alert" class="alert__icon" />
                        <div>
                            <p class="alert__title">Your message wasn't sent yet</p>
                            <p class="alert__text">
                                {{ $errors->count() === 1
                                    ? 'One field needs attention — it is highlighted below.'
                                    : $errors->count().' fields need attention — they are highlighted below.' }}
                            </p>
                        </div>
                    </div>
                @endif

                <form class="contact-form" method="POST" action="{{ route('contact.store') }}" data-contact-form>
                    @csrf

                    <div class="contact-form__row">
                        <x-form.input
                            name="name"
                            label="Your name"
                            autocomplete="name"
                            placeholder="Jane Doe"
                            :required="true"
                            :maxlength="80"
                        />

                        <x-form.input
                            name="email"
                            label="Email address"
                            type="email"
                            autocomplete="email"
                            placeholder="you@example.com"
                            :required="true"
                            :maxlength="120"
                        />
                    </div>

                    <div class="contact-form__row">
                        <x-form.input
                            name="phone"
                            label="Phone"
                            type="tel"
                            inputmode="tel"
                            autocomplete="tel"
                            placeholder="+1 555 000 0000"
                            hint="Only if you'd prefer a call."
                            :maxlength="30"
                        />

                        <x-form.input
                            name="subject"
                            label="Subject"
                            placeholder="New Laravel application"
                            :required="true"
                            :maxlength="140"
                        />
                    </div>

                    <x-form.select
                        name="budget"
                        label="Budget range"
                        placeholder="Select a range (optional)"
                        :options="$budgets"
                        hint="A rough range is enough — it just helps me judge scope."
                    />

                    <x-form.textarea
                        name="message"
                        label="Message"
                        placeholder="What are you building, and what would you like help with?"
                        hint="A few sentences is plenty. Minimum 20 characters."
                        :required="true"
                        :maxlength="2000"
                        :rows="7"
                    />

                    {{-- Honeypot: hidden from people, filled by bots. --}}
                    <div class="honeypot" aria-hidden="true">
                        <label for="website">Website</label>
                        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="contact-form__actions">
                        <button class="btn btn--primary" type="submit" data-submit-button>
                            <span class="btn__label">Send message</span>
                            <x-icon name="send" />
                        </button>
                        <p class="contact-form__note">Your details are only used to reply to this enquiry.</p>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
