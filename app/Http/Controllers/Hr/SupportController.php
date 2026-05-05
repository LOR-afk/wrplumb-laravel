<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\AlertService;

class SupportController extends Controller
{
    public function index()
    {
        /*
         * Display one queued conversation per client only.
         * If the same client has multiple HR-routed conversations,
         * show only the latest conversation in the HR queue.
         */
        $latestConversationIds = SupportConversation::query()
            ->where('current_queue', 'hr')
            ->selectRaw('MAX(id) as id')
            ->groupBy('client_id')
            ->pluck('id');

        $conversations = SupportConversation::with('client')
            ->whereIn('id', $latestConversationIds)
            ->latest()
            ->paginate(10);

        return view('hr.support.index', compact('conversations'));
    }

    public function show(SupportConversation $conversation)
    {
        abort_if($conversation->current_queue !== 'hr', 404);

        $conversation->load([
            'client',
            'messages' => function ($query) {
                $query->orderBy('created_at');
            },
        ]);

        return view('hr.support.show', compact('conversation'));
    }

    public function reply(Request $request, SupportConversation $conversation)
    {
        abort_if($conversation->current_queue !== 'hr', 404);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        SupportMessage::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'hr',
            'sender_id' => Auth::id(),
            'message' => $validated['message'],
        ]);

        $conversation->update([
            'status' => 'open',
            'current_queue' => 'hr',
        ]);

        return back()->with('success', 'Reply sent successfully.');
    }

    public function escalateToAdmin(SupportConversation $conversation)
    {
        abort_if($conversation->current_queue !== 'hr', 404);

        $conversation->update([
            'current_queue' => 'admin',
            'routed_to' => 'admin',
            'routed_at' => now(),
            'status' => 'escalated',
        ]);

        AlertService::sendToRole(
            'admin',
            'Support escalated by HR',
            'A support conversation has been escalated to Admin for further review.',
            route('admin.support.show', $conversation),
            'warning'
        );

        return redirect()
            ->route('hr.support.index')
            ->with('success', 'Conversation escalated to Admin successfully.');
    }
}
