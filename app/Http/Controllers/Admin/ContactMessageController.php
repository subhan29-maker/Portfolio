<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function index(): View
    {
        $messages = ContactMessage::query()
            ->latest()
            ->paginate(20);

        return view('admin.messages.index', [
            'messages' => $messages,
            'unreadCount' => ContactMessage::query()->whereNull('read_at')->count(),
        ]);
    }

    public function show(ContactMessage $message): View
    {
        if (is_null($message->read_at)) {
            $message->forceFill(['read_at' => now()])->save();
        }

        return view('admin.messages.show', [
            'message' => $message,
        ]);
    }

    public function markRead(ContactMessage $message): RedirectResponse
    {
        $message->forceFill(['read_at' => now()])->save();

        return back()->with('status', 'Message marked as read.');
    }

    public function markUnread(ContactMessage $message): RedirectResponse
    {
        $message->forceFill(['read_at' => null])->save();

        return back()->with('status', 'Message marked as unread.');
    }

    public function updateStatus(Request $request, ContactMessage $message): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:new,read,replied,archived,pending'],
        ]);

        // Use existing notified_owner/read_at as a simple mechanism if status column doesn't exist
        if (array_key_exists('status', $message->getAttributes()) || method_exists($message, 'setStatusAttribute')) {
            $message->status = $data['status'];
        }

        if ($data['status'] !== 'new' && is_null($message->read_at)) {
            $message->read_at = now();
        } elseif ($data['status'] === 'new' && ! is_null($message->read_at) && $request->has('mark_unread')) {
            $message->read_at = null;
        }

        $message->save();

        return back()->with('status', 'Status updated.');
    }

    public function destroy(ContactMessage $message): RedirectResponse
    {
        $message->delete();

        return redirect()->route('admin.messages.index')->with('status', 'Message deleted.');
    }
}
