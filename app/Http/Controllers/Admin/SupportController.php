<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    public function index(Request $request)
    {
        $baseQuery = SupportConversation::query()
            ->where(function ($query) {
                $query->where('current_queue', 'admin')
                    ->orWhere('status', 'resolved');
            });

        $summary = [
            'open' => (clone $baseQuery)->where('status', 'open')->count(),
            'escalated' => (clone $baseQuery)->where('current_queue', 'admin')->where('status', 'routed')->count(),
            'waiting' => (clone $baseQuery)->where('status', 'routed')->count(),
            'resolved_today' => (clone $baseQuery)
                ->where('status', 'resolved')
                ->whereDate('resolved_at', today())
                ->count(),
        ];

        $query = SupportConversation::with(['client', 'messages' => fn ($q) => $q->orderBy('created_at')])
            ->withCount('messages')
            ->where(function ($q) {
                $q->where('current_queue', 'admin')
                    ->orWhere('status', 'resolved');
            });

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->whereHas('client', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                })->orWhereHas('messages', function ($q) use ($search) {
                    $q->where('message', 'like', "%{$search}%");
                });
            });
        }

        $conversations = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $selectedConversation = null;
        $selectedId = $request->integer('conversation');

        if ($selectedId) {
            $selectedConversation = SupportConversation::with([
                    'client',
                    'messages' => fn ($q) => $q->orderBy('created_at'),
                ])
                ->withCount('messages')
                ->where(function ($q) {
                    $q->where('current_queue', 'admin')
                        ->orWhere('status', 'resolved');
                })
                ->find($selectedId);
        }

        if (!$selectedConversation && $conversations->count()) {
            $selectedConversation = SupportConversation::with([
                    'client',
                    'messages' => fn ($q) => $q->orderBy('created_at'),
                ])
                ->withCount('messages')
                ->find($conversations->first()->id);
        }

        return view('admin.support.index', compact('conversations', 'summary', 'selectedConversation'));
    }

    public function show(SupportConversation $conversation)
    {
        abort_if($conversation->current_queue !== 'admin' && $conversation->status !== 'resolved', 404);

        $conversation->load([
            'client',
            'messages' => fn ($q) => $q->orderBy('created_at'),
        ]);

        return view('admin.support.show', compact('conversation'));
    }

    public function reply(Request $request, SupportConversation $conversation)
    {
        abort_if($conversation->current_queue !== 'admin', 404);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        SupportMessage::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'admin',
            'sender_id' => Auth::id(),
            'message' => $validated['message'],
        ]);

        $conversation->update([
            'status' => 'open',
            'current_queue' => 'admin',
        ]);

        return back()->with('success', 'Reply sent successfully.');
    }

    public function resolve(SupportConversation $conversation)
    {
        abort_if($conversation->current_queue !== 'admin' && $conversation->status !== 'resolved', 404);

        if ($conversation->status === 'resolved') {
            return back()->with('info', 'Conversation is already resolved.');
        }

        $conversation->update([
            'status' => 'resolved',
            'current_queue' => 'resolved',
            'resolved_at' => now(),
        ]);

        SupportMessage::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'system',
            'sender_id' => null,
            'message' => 'This conversation has been resolved.',
        ]);

        return back()->with('success', 'Conversation marked as resolved.');
    }
}
