@extends('layouts.admin')

@section('title', 'View Message')

@section('actions')
    <a class="btn btn--ghost btn--sm" href="{{ route('admin.messages.index') }}">
        <x-icon name="arrow-left" />
        Back to messages
    </a>
@endsection

@section('content')
    <section class="admin-section">
        <div class="admin-card">
            <div class="admin-card__head">
                <div>
                    <h1 class="admin-card__title">{{ $message->subject }}</h1>
                    <p class="admin-card__meta">From {{ $message->name }} &lt;{{ $message->email }}&gt; · {{ $message->created_at->toDayDateTimeString() }}</p>
                </div>
                <div class="chip-list">
                    @if (is_null($message->read_at))
                        <form method="POST" action="{{ route('admin.messages.read', $message) }}">
                            @csrf
                            <button class="btn btn--ghost btn--sm">Mark as read</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.messages.unread', $message) }}">
                            @csrf
                            <button class="btn btn--ghost btn--sm">Mark as unread</button>
                        </form>
                    @endif
                    <a class="btn btn--primary btn--sm" href="mailto:{{ $message->email }}?subject=Re: {{ rawurlencode($message->subject) }}">Reply by email</a>
                    <form method="POST" action="{{ route('admin.messages.destroy', $message) }}" onsubmit="return confirm('Delete this message?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn--ghost btn--sm">Delete</button>
                    </form>
                </div>
            </div>
            <div class="admin-card__body">
                <div class="admin-form-grid">
                    <div>
                        <div class="admin-field__label">Name</div>
                        <div>{{ $message->name }}</div>
                    </div>
                    <div>
                        <div class="admin-field__label">Email</div>
                        <div><a href="mailto:{{ $message->email }}">{{ $message->email }}</a></div>
                    </div>
                    @if ($message->phone)
                        <div>
                            <div class="admin-field__label">Phone</div>
                            <div>{{ $message->phone }}</div>
                        </div>
                    @endif
                    @if ($message->budget)
                        <div>
                            <div class="admin-field__label">Budget</div>
                            <div>{{ is_object($message->budget) ? $message->budget->label() : $message->budget }}</div>
                        </div>
                    @endif
                </div>
                <hr style="border:none;border-top:1px solid var(--border);margin:1.5rem 0;">
                <div style="white-space:pre-line;line-height:1.7;">{{ $message->message }}</div>
            </div>
        </div>
    </section>
@endsection
