<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\MessageThread;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function index()
    {
        $threads = MessageThread::forUser(Auth::user())
            ->with(['latestMessage.sender', 'participants'])
            ->withCount(['messages'])
            ->orderByDesc(
                Message::select('created_at')
                    ->whereColumn('thread_id', 'message_threads.id')
                    ->latest()
                    ->take(1)
            )
            ->paginate(20);

        $unreadCount = MessageThread::forUser(Auth::user())
            ->withUnread(Auth::user())
            ->count();

        return view('messages.index', compact('threads', 'unreadCount'));
    }

    public function show(MessageThread $thread)
    {
        // Check if user is participant
        if (!$thread->participants()->where('user_id', Auth::id())->exists()) {
            abort(403);
        }

        $thread->markAsReadFor(Auth::user());

        $messages = $thread->messages()->with('sender')->get();
        $participants = $thread->participants;

        return view('messages.show', compact('thread', 'messages', 'participants'));
    }

    public function create(Request $request)
    {
        $recipient = null;
        if ($request->has('to')) {
            $recipient = User::find($request->to);
        }

        $subject = $request->get('subject');

        return view('messages.create', compact('recipient', 'subject'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $recipient = User::findOrFail($request->recipient_id);

        // Don't allow sending to yourself
        if ($recipient->id === Auth::id()) {
            return back()->withErrors(['recipient_id' => 'Нельзя отправить сообщение самому себе.']);
        }

        // Create thread
        $thread = MessageThread::create([
            'subject' => $request->subject,
        ]);

        // Add participants
        $thread->participants()->attach([
            Auth::id() => ['is_read' => true, 'last_read_at' => now()],
            $recipient->id => ['is_read' => false],
        ]);

        // Create message
        $thread->messages()->create([
            'sender_id' => Auth::id(),
            'body' => $request->body,
        ]);

        return redirect()->route('messages.show', $thread)
            ->with('success', 'Сообщение отправлено.');
    }

    public function reply(Request $request, MessageThread $thread)
    {
        // Check if user is participant
        if (!$thread->participants()->where('user_id', Auth::id())->exists()) {
            abort(403);
        }

        $request->validate([
            'body' => 'required|string',
        ]);

        // Create message
        $thread->messages()->create([
            'sender_id' => Auth::id(),
            'body' => $request->body,
        ]);

        // Mark as unread for other participants
        $thread->participants()
            ->where('user_id', '!=', Auth::id())
            ->update(['is_read' => false]);

        // Mark as read for sender
        $thread->markAsReadFor(Auth::user());

        return redirect()->route('messages.show', $thread)
            ->with('success', 'Ответ отправлен.');
    }

    public function destroy(MessageThread $thread)
    {
        // Check if user is participant
        if (!$thread->participants()->where('user_id', Auth::id())->exists()) {
            abort(403);
        }

        // Soft delete for this user only
        $thread->participants()->updateExistingPivot(Auth::id(), [
            'is_deleted' => true,
        ]);

        return redirect()->route('messages.index')
            ->with('success', 'Переписка удалена.');
    }

    public function searchUsers(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $users = User::where('id', '!=', Auth::id())
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'email']);

        return response()->json($users);
    }
}
