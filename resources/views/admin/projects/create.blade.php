@extends('layouts.admin')

@section('title', 'New project')

@section('actions')
    <a class="btn btn--ghost btn--sm" href="{{ route('admin.projects.index') }}">Back to projects</a>
@endsection

@section('content')
    <section class="admin-section">
        <form method="POST" action="{{ route('admin.projects.store') }}" enctype="multipart/form-data">
            @csrf

            @include('admin.projects._form', ['project' => null])
        </form>
    </section>
@endsection
