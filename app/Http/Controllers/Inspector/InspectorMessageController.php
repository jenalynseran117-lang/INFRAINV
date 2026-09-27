<?php

namespace App\Http\Controllers\Inspector;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WorkNote;
use App\Models\WorkNoteRead;
use App\Models\WorkNoteTyping;

class InspectorMessageController extends Controller
{
    // How long a "typing" signal stays valid before we treat it as stale.
    // Matches the client's polling interval so the indicator disappears
    // shortly after someone stops typing (or closes the tab) without
    // needing an explicit "stopped typing" event from the client.
    const TYPING_TIMEOUT_SECONDS = 4;

    public function MessageIndex()
    {
        // eager-load user so we can show real names/initials for avatars
        // instead of just the role
        $notes = WorkNote::with('user')->orderBy('created_at', 'asc')->get();

        // i-mark as "read" yung Work Notes page para sa kasalukuyang user
        // (ito yung gagamitin ng WorkNoteComposer para i-reset yung badge)
        WorkNoteRead::updateOrCreate(
            ['user_id' => auth()->id()],
            ['last_read_at' => now()]
        );

        return view('inspector.Message.WorknoteIndex', compact('notes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        $note = WorkNote::create([
            'user_id' => auth()->id(),
            'role' => auth()->user()->role,
            'message' => $request->message
        ]);

        // Sending implicitly means you're done typing — clear it right away
        // instead of waiting for the timeout to expire on its own.
        // (Typing status lives in work_note_typings, not work_note_reads —
        // see the typing()/poll() methods below.)
        WorkNoteTyping::where('user_id', auth()->id())->delete();

        // Return the full note (with the sender's name) so the frontend can
        // append it to the thread immediately, without a full page reload.
        return response()->json([
            'success' => true,
            'note' => [
                'id'         => $note->id,
                'user_id'    => $note->user_id,
                'user_name'  => auth()->user()->name,
                'role'       => $note->role,
                'message'    => $note->message,
                'created_at' => $note->created_at->toIso8601String(),
            ],
        ]);
    }

    // Called (debounced) from the client while the user has text in the
    // input box, so other participants can see "X is typing...". Uses the
    // work_note_typings table (one row per user, unique on user_id) —
    // updated_at doubles as "last typed at".
    public function typing(Request $request)
    {
        WorkNoteTyping::updateOrCreate(
            ['user_id' => auth()->id()],
            ['role' => auth()->user()->role]
        );

        return response()->json(['success' => true]);
    }

    // Polled every few seconds by the client to pick up new messages and
    // who (besides yourself) is currently typing, without a full reload.
    public function poll(Request $request)
    {
        $afterId = (int) $request->query('after', 0);

        $notes = WorkNote::with('user')
            ->where('id', '>', $afterId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn ($note) => [
                'id'         => $note->id,
                'user_id'    => $note->user_id,
                'user_name'  => $note->user->name ?? $note->role,
                'role'       => $note->role,
                'message'    => $note->message,
                'created_at' => $note->created_at->toIso8601String(),
            ]);

        $typingUsers = WorkNoteTyping::with('user')
            ->where('user_id', '!=', auth()->id())
            ->where('updated_at', '>=', now()->subSeconds(self::TYPING_TIMEOUT_SECONDS))
            ->get()
            ->map(fn ($t) => [
                'user_name' => $t->user->name ?? 'Someone',
                'role'      => $t->role,
            ]);

        return response()->json([
            'notes'  => $notes,
            'typing' => $typingUsers,
        ]);
    }
}