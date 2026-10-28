@extends('layouts.admin')

@section('title', 'Profile & settings')

@section('content')
    <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <section class="admin-section" aria-label="Profile picture">
            <div class="admin-card">
                <div class="admin-card__head">
                    <h2 class="admin-card__title">Profile picture</h2>
                </div>

                <div class="admin-card__body">
                    <p class="admin-media__caption" style="margin-bottom: 1rem;">
                        Used on the public homepage and About page.
                        @if ($picture)
                            Your uploaded picture is shown instead of the default one.
                        @else
                            The default picture is in use until you upload one.
                        @endif
                    </p>

                    <div class="admin-media admin-media--current">
                        <img
                            class="admin-media__preview admin-media__preview--contain"
                            id="profile-preview"
                            src="{{ $picture['photo'] ?? asset($defaultPhoto) }}"
                            alt="{{ $pictureAlt }}"
                        >

                        <div>
                            @if ($picture)
                                <p class="admin-media__caption">{{ $picturePath }}</p>
                                <p class="admin-media__caption">Uploaded picture</p>
                            @else
                                <p class="admin-media__caption">{{ $defaultPhoto }}</p>
                                <p class="admin-media__caption">Default picture</p>
                            @endif
                        </div>
                    </div>

                    <div style="margin-top: 1.1rem;">
                        <x-form.file
                            name="photo"
                            label="Upload a new picture"
                            hint="JPG, PNG or WebP, up to 5 MB. A square or portrait photo works best."
                            preview="#profile-preview"
                        />
                    </div>

                    <div style="margin-top: 1.1rem;">
                        <x-form.input
                            name="photo_alt"
                            label="Picture description"
                            :value="$pictureAlt"
                            hint="Describes the picture for search engines and screen readers."
                        />
                    </div>
                </div>
            </div>
        </section>

        <section class="admin-section" aria-label="Portfolio details">
            <div class="admin-card">
                <div class="admin-card__head">
                    <h2 class="admin-card__title">Portfolio details</h2>
                </div>

                <div class="admin-card__body">
                    <p class="admin-media__caption" style="margin-bottom: 1.1rem;">
                        These values are shown across the public site. Clearing a field and saving hides it.
                    </p>

                    <div class="admin-form-grid admin-form-grid--wide">
                        <x-form.input name="name" label="Name" :value="$values['name']" required />

                        <x-form.input name="role" label="Professional title" :value="$values['role']" required />

                        <x-form.input
                            name="hero_label"
                            label="Hero label"
                            :value="$values['hero_label']"
                            hint="The small line above your name on the homepage."
                        />

                        <x-form.input
                            name="hero_headline"
                            label="Hero headline"
                            :value="$values['hero_headline']"
                        />

                        <div class="field--full">
                            <x-form.textarea
                                name="hero_lead"
                                label="Introduction"
                                :value="$values['hero_lead']"
                                hint="The short introduction shown under your headline."
                                :rows="3"
                            />
                        </div>

                        <div class="field--full">
                            <x-form.input
                                name="availability"
                                label="Availability line"
                                :value="$values['availability']"
                                hint="For example: Available for new projects."
                            />
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="admin-section" aria-label="Contact and social links">
            <div class="admin-card">
                <div class="admin-card__head">
                    <h2 class="admin-card__title">Contact &amp; social links</h2>
                </div>

                <div class="admin-card__body">
                    <p class="admin-media__caption" style="margin-bottom: 1.1rem;">
                        Hidden from the interface while they stay empty.
                    </p>

                    <div class="admin-form-grid">
                        <x-form.input name="email" label="Email address" type="email" :value="$values['email']" />
                        <x-form.input name="phone" label="Phone" :value="$values['phone']" />
                        <x-form.input name="location" label="Location" :value="$values['location']" />

                        <div class="field--full">
                            <x-form.input name="github" label="GitHub URL" type="url" :value="$values['github']" placeholder="https://github.com/you" />
                        </div>

                        <div class="field--full">
                            <x-form.input name="linkedin" label="LinkedIn URL" type="url" :value="$values['linkedin']" placeholder="https://www.linkedin.com/in/you" />
                        </div>

                        <div class="field--full">
                            <x-form.input name="x" label="X URL" type="url" :value="$values['x']" placeholder="https://x.com/you" />
                        </div>
                    </div>
                </div>

                <div class="admin-card__foot">
                    <button class="btn btn--primary" type="submit">
                        <x-icon name="check" />
                        Save profile
                    </button>
                </div>
            </div>
        </section>
    </form>

    <section class="admin-section" aria-label="Default picture">
        <div class="admin-card">
            <div class="admin-card__head">
                <h2 class="admin-card__title">Reset picture</h2>
            </div>

            <div class="admin-card__body">
                <p class="admin-media__caption" style="margin-bottom: 1rem;">
                    Revert to the default picture shipped with the portfolio. Your uploaded picture is deleted
                    and cannot be recovered, so download it first if you want to keep it.
                </p>

                @if ($picture)
                    <form
                        method="POST"
                        action="{{ route('admin.profile.picture.destroy') }}"
                        data-confirm="Use the default profile picture again? Your uploaded picture will be permanently deleted."
                    >
                        @csrf
                        @method('DELETE')

                        <button class="btn btn--ghost btn--sm" type="submit">
                            <x-icon name="trash" />
                            Use the default picture
                        </button>
                    </form>
                @else
                    <p class="admin-media__caption">The default picture is already in use.</p>
                @endif
            </div>
        </div>
    </section>
@endsection
