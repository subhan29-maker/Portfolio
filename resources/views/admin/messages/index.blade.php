@extends('layouts.admin')

@section('title', 'Contact Messages')

@section('content')
    <section class="admin-section">
        <div class="admin-card">
            <div class="admin-card__head">
                <h1 class="admin-card__title">Contact Messages</h1>
                <span class="badge {{ ($unreadCount ?? 0) > 0 ? 'badge--warning' : 'badge--muted' }}">
                    {{ $unreadCount ?? 0 }} unread
                </span>
            </div>

            @if ($messages->isEmpty())
                <div class="admin-card__body">
                    <div class="empty-state">
                        <h3>No messages yet</h3>
                        <p>Messages from the contact form will appear here.</p>
                    </div>
                </div>
            @else
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>From</th>
                                <th>Subject</th>
                                <th>Received</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($messages as $msg)
                                <tr>
                                    <td>
                                        @if (is_null($msg->read_at))
                                            <span class="badge badge--warning">New</span>
                                        @else
                                            <span class="badge badge--muted">Read</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="admin-table__main">
                                            <div class="admin-table__title">{{ $msg->name }}</div>
                                            <div class="admin-table__meta">{{ $msg->email }}</div>
                                        </div>
                                    </td>
                                    <td>{{ Str::limit($msg->subject, 60) }}</td>
                                    <td>{{ $msg->created_at->diffForHumans() }}</td>
                                    <td class="admin-table__actions">
                                        <a class="btn btn--ghost btn--sm" href="{{ route('admin.messages.show', $msg) }}">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="admin-card__body">
                    {{ $messages->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
