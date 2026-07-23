<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

/**
 * The contact form's inbox. Opening a message marks it read — the unread count
 * in the sidebar is what tells someone there is work here.
 */
class MessageController extends Controller
{
    public function index(Request $request)
    {
        $messages = ContactMessage::query()
            ->when($request->string('q')->trim()->value(), function ($q, $term) {
                $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%';
                $q->where(fn ($w) => $w->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('subject', 'like', $like)
                    ->orWhere('message', 'like', $like));
            })
            ->when($request->input('filter') === 'unread', fn ($q) => $q->whereNull('read_at'))
            ->when($request->input('filter') === 'read', fn ($q) => $q->whereNotNull('read_at'))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.messages.index', [
            'active' => 'messages',
            'messages' => $messages,
            'unread' => ContactMessage::whereNull('read_at')->count(),
        ]);
    }

    public function show(ContactMessage $message)
    {
        $message->read_at ??= now();
        $message->save();

        return view('admin.messages.show', [
            'active' => 'messages',
            'message' => $message,
        ]);
    }

    /**
     * Put a message back in the unread pile — for when someone opens one they
     * do not have time to answer.
     */
    public function unread(ContactMessage $message)
    {
        $message->update(['read_at' => null]);

        return redirect()
            ->route('admin.messages.index')
            ->with('status', 'Marked unread.');
    }

    public function destroy(ContactMessage $message)
    {
        $message->delete();

        return redirect()
            ->route('admin.messages.index')
            ->with('status', 'Message deleted.');
    }
}
