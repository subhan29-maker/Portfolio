<?php

namespace App\Http\Controllers;

use App\Enums\BudgetRange;
use App\Http\Requests\ContactMessageRequest;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('contact', [
            'budgets' => BudgetRange::options(),
        ]);
    }

    public function store(ContactMessageRequest $request): RedirectResponse
    {
        $message = ContactMessage::create([
            ...$request->safe()->only(['name', 'email', 'phone', 'subject', 'budget', 'message']),
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 255, ''),
        ]);

        $notified = $this->notifyOwner($message);

        // The message is stored in the database either way, so the confirmation
        // never claims an email was delivered unless one actually was.
        return redirect()
            ->route('contact')
            ->with('contact_status', $notified
                ? 'Thanks, '.$message->name.'. Your message has been received and a notification has been sent.'
                : 'Thanks, '.$message->name.'. Your message has been received.');
    }

    /**
     * Email the site owner when a recipient address is configured.
     *
     * A delivery problem must not fail the visitor's request, so failures are
     * reported and the submission stays available in the database.
     */
    private function notifyOwner(ContactMessage $message): bool
    {
        $recipient = config('portfolio.contact.notify_email');

        if (blank($recipient)) {
            return false;
        }

        try {
            Mail::to($recipient)->send(new ContactMessageReceived($message));
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }

        $message->forceFill(['notified_owner' => true])->save();

        return true;
    }
}
