@php
    /** @var \App\Models\Project|null $project */
    $isEdit = isset($project) && $project instanceof \App\Models\Project;

    $action = $isEdit ? route('admin.projects.update', $project) : route('admin.projects.store');

    // A stored value, or the submitted one, or nothing at all.
    $value = fn (string $key, ?string $default = null) => old($key, $isEdit ? ($project->{$key} ?? $default) : $default);

    // List fields are stored as arrays but edited as plain text.
    $lines = fn (string $key) => old($key, $isEdit ? implode("\n", (array) ($project->{$key} ?? [])) : '');
    $tags = fn (string $key) => old($key, $isEdit ? implode(', ', (array) ($project->{$key} ?? [])) : '');

    $statuses = \App\Enums\ProjectStatus::options();
@endphp

<div class="admin-columns">
    <div class="admin-column">
        {{-- Main details --}}
        <section class="admin-card">
            <div class="admin-card__head">
                <h2 class="admin-card__title">Project details</h2>
            </div>

            <div class="admin-card__body">
                <div class="admin-form-grid admin-form-grid--wide">
                    <x-form.input
                        name="title"
                        label="Title"
                        :value="$value('title')"
                        placeholder="ThinkCode CRM"
                        required
                    />

                    <x-form.input
                        name="slug"
                        label="Slug"
                        :value="$value('slug')"
                        hint="Leave empty to keep the current one, or to build it from the title."
                        placeholder="thinkcode-crm"
                    />

                    <div class="field--full">
                        <x-form.textarea
                            name="summary"
                            label="Summary"
                            :value="$value('summary')"
                            hint="One or two sentences. Shown on project cards and in search results. Up to 300 characters."
                            :rows="3"
                        />
                    </div>

                    <div class="field--full">
                        <x-form.textarea
                            name="description"
                            label="Detailed description"
                            :value="$value('description')"
                            hint="The full description shown on the project's public page."
                            :rows="8"
                        />
                    </div>
                </div>
            </div>
        </section>

        {{-- Story --}}
        <section class="admin-card">
            <div class="admin-card__head">
                <h2 class="admin-card__title">The story behind it</h2>
            </div>

            <div class="admin-card__body">
                <div class="admin-form-grid admin-form-grid--wide">
                    <div class="field--full">
                        <x-form.textarea
                            name="problem"
                            label="Problem it solves"
                            :value="$value('problem')"
                            hint="What situation or difficulty existed before this project."
                            :rows="4"
                        />
                    </div>

                    <div class="field--full">
                        <x-form.textarea
                            name="purpose"
                            label="Why it was built"
                            :value="$value('purpose')"
                            hint="What the project was built to achieve."
                            :rows="4"
                        />
                    </div>

                    <div class="field--full">
                        <x-form.textarea
                            name="contribution"
                            label="Your contribution"
                            :value="$value('contribution')"
                            hint="What you personally built or owned on this project."
                            :rows="4"
                        />
                    </div>
                </div>
            </div>
        </section>

        {{-- Detail page content --}}
        <section class="admin-card">
            <div class="admin-card__head">
                <h2 class="admin-card__title">Detail page content</h2>
            </div>

            <div class="admin-card__body">
                <div class="admin-form-grid admin-form-grid--wide">
                    <div class="field--full">
                        <x-form.textarea
                            name="features"
                            label="Features"
                            :value="$lines('features')"
                            hint="One feature per line."
                            :rows="6"
                        />
                    </div>

                    <div class="field--full">
                        <x-form.textarea
                            name="highlights"
                            label="Highlights"
                            :value="$lines('highlights')"
                            hint="One highlight per line."
                            :rows="4"
                        />
                    </div>
                </div>
            </div>
        </section>

        {{-- Technology and links --}}
        <section class="admin-card">
            <div class="admin-card__head">
                <h2 class="admin-card__title">Technology &amp; links</h2>
            </div>

            <div class="admin-card__body">
                <div class="admin-form-grid">
                    <div class="field--full">
                        <x-form.input
                            name="technologies"
                            label="Technologies"
                            :value="$tags('technologies')"
                            hint="Comma separated, for example: Laravel, MySQL, Redis."
                        />
                    </div>

                    <x-form.input
                        name="category"
                        label="Category"
                        :value="$value('category')"
                        hint="Used by the dashboard filters."
                        placeholder="Web app"
                    />

                    <x-form.input
                        name="role"
                        label="Your role"
                        :value="$value('role')"
                        placeholder="Full-stack developer"
                    />

                    <x-form.input
                        name="year"
                        label="Year"
                        :value="$value('year')"
                        placeholder="2026"
                    />

                    <div class="field--full">
                        <x-form.input
                            name="live_demo_url"
                            label="Live demo URL"
                            type="url"
                            :value="$value('live_demo_url')"
                            placeholder="https://example.com"
                        />
                    </div>

                    <div class="field--full">
                        <x-form.input
                            name="repository_url"
                            label="Repository URL"
                            type="url"
                            :value="$value('repository_url')"
                            placeholder="https://github.com/you/project"
                        />
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="admin-column admin-column--narrow">
        {{-- Publishing --}}
        <section class="admin-card">
            <div class="admin-card__head">
                <h2 class="admin-card__title">Publishing</h2>
            </div>

            <div class="admin-card__body">
                <x-form.select
                    name="status"
                    label="Status"
                    :options="$statuses"
                    :value="$value('status', \App\Enums\ProjectStatus::Draft->value)"
                    hint="Only published projects appear on the public portfolio."
                    required
                />

                <x-form.input
                    name="sort_order"
                    label="Display position"
                    type="number"
                    :value="$value('sort_order', (string) $nextSortOrder)"
                    hint="Lower numbers appear first on the public project pages."
                />

                <div class="admin-checkbox-stack">
                    <x-form.checkbox
                        name="is_featured"
                        label="Featured project"
                        hint="Marked as showcase work in the dashboard."
                        :checked="(bool) old('is_featured', $isEdit ? $project->is_featured : false)"
                    />

                    <x-form.checkbox
                        name="is_demo"
                        label="Demo placeholder"
                        hint="Labelled honestly on the public site so it is never mistaken for real client work."
                        :checked="(bool) old('is_demo', $isEdit ? $project->is_demo : false)"
                    />
                </div>
            </div>
        </section>

        {{-- Cover image --}}
        <section class="admin-card">
            <div class="admin-card__head">
                <h2 class="admin-card__title">Cover image</h2>
            </div>

            <div class="admin-card__body">
                @if ($isEdit && $project->coverUrl(false))
                    <div class="admin-media admin-media--current">
                        <img class="admin-media__preview" src="{{ $project->coverUrl(false) }}" alt="Current cover for {{ $project->title }}">

                        <div>
                            <p class="admin-media__caption">{{ $project->cover_image }}</p>

                            <x-form.checkbox
                                name="remove_cover"
                                label="Remove the current cover"
                                hint="The image is deleted from storage when you save."
                                :checked="old('remove_cover') !== null"
                            />
                        </div>
                    </div>
                @endif

                <x-form.file
                    name="cover_image"
                    label="Upload a new cover"
                    hint="JPG, PNG or WebP, up to 4 MB. A wide screenshot works best."
                    preview="#cover-preview"
                />

                <p class="admin-media__caption">Preview</p>
                <img
                    class="admin-media__preview"
                    id="cover-preview"
                    src="{{ $isEdit ? ($project->coverUrl(false) ?? '') : '' }}"
                    alt=""
                    @unless ($isEdit && $project->coverUrl(false)) hidden @endunless
                >
            </div>
        </section>

        {{-- Gallery --}}
        <section class="admin-card">
            <div class="admin-card__head">
                <h2 class="admin-card__title">Gallery</h2>
            </div>

            <div class="admin-card__body">
                <p class="admin-media__caption" style="margin-bottom: 0.85rem;">
                    Extra screenshots shown on the public project page, after the cover.
                </p>

                @if ($isEdit && $project->galleryUrls(false) !== [])
                    <div class="admin-media__grid" style="margin-bottom: 1rem;">
                        @foreach ($project->galleryUrls(false) as $index => $src)
                            <div class="admin-media__item">
                                <img class="admin-media__preview" src="{{ $src }}" alt="Gallery image {{ $index + 1 }} for {{ $project->title }}">

                                <x-form.checkbox
                                    name="remove_gallery[]"
                                    label="Remove"
                                    value="{{ $project->gallery[$index] }}"
                                    :checked="in_array($project->gallery[$index], (array) old('remove_gallery', []), true)"
                                />
                            </div>
                        @endforeach
                    </div>
                @endif

                <x-form.file
                    name="gallery[]"
                    label="Add images"
                    hint="JPG, PNG or WebP, up to 4 MB each. Choose more than one at once if you like."
                    :required="false"
                    multiple
                />
            </div>
        </section>

        <div class="admin-card__foot" style="border: 1px solid var(--border); border-radius: var(--radius); background: var(--surface);">
            <button class="btn btn--primary" type="submit">
                <x-icon name="check" />
                {{ $isEdit ? 'Save changes' : 'Create project' }}
            </button>
        </div>
    </div>
</div>
