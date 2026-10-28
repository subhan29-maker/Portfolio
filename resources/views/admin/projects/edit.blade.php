@extends('layouts.admin')

@section('title', 'Edit project')

@section('actions')
    <a class="btn btn--ghost btn--sm" href="{{ route('admin.projects.index') }}">Back to projects</a>

    @if ($project->isPublished())
        <a class="btn btn--ghost btn--sm" href="{{ route('projects.show', $project->slug) }}" target="_blank" rel="noopener">
            <x-icon name="external-link" />
            View live
        </a>
    @endif
@endsection

@section('content')
    <section class="admin-section">
        <form method="POST" action="{{ route('admin.projects.update', $project) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            @include('admin.projects._form')
        </form>
    </section>

    <section class="admin-section">
        <div class="admin-card">
            <div class="admin-card__head">
                <h2 class="admin-card__title">Danger zone</h2>
            </div>

            <div class="admin-card__body">
                <p class="admin-media__caption" style="margin-bottom: 1rem;">
                    Deleting removes this project and its uploaded images from storage. This cannot be undone.
                </p>

                <form
                    method="POST"
                    action="{{ route('admin.projects.destroy', $project) }}"
                    data-confirm="Delete {{ $project->title }} permanently? This also removes its uploaded images. This cannot be undone."
                >
                    @csrf
                    @method('DELETE')

                    <button class="btn btn--ghost btn--sm" type="submit">
                        <x-icon name="trash" />
                        Delete this project
                    </button>
                </form>
            </div>
        </div>
    </section>
@endsection
