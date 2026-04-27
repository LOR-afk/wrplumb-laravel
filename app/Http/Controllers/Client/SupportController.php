<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    public function index()
    {
        $conversation = SupportConversation::with([
                'messages' => function ($query) {
                    $query->orderBy('created_at');
                }
            ])
            ->where('client_id', Auth::id())
            ->latest()
            ->first();

        return view('client.support.index', compact('conversation'));
    }

    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $conversation = SupportConversation::firstOrCreate(
            [
                'client_id' => Auth::id(),
                'status' => 'open',
            ],
            [
                'current_queue' => 'bot',
                'routed_to' => null,
                'routed_at' => null,
                'resolved_at' => null,
            ]
        );

        SupportMessage::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'client',
            'sender_id' => Auth::id(),
            'message' => $validated['message'],
        ]);

        $message = strtolower($validated['message']);
        $botReply = "I'm sorry, I couldn't find a clear answer to that. I'm redirecting your concern to HR for further assistance.";
        $routeTo = 'hr';

        if (str_contains($message, 'quotation') || str_contains($message, 'request quote')) {
            $botReply = 'To request a quotation, go to the homepage and fill out the Free Quotation Request form.';
            $routeTo = 'bot';
        } elseif (str_contains($message, 'track') || str_contains($message, 'status') || str_contains($message, 'my request')) {
            $botReply = 'You can track your request from your Client Dashboard under My Requests.';
            $routeTo = 'bot';
        } elseif (str_contains($message, 'contact') || str_contains($message, 'support')) {
            $botReply = 'You can chat here first. If the concern needs manual help, it will be routed automatically.';
            $routeTo = 'bot';
        }

        SupportMessage::create([
            'conversation_id' => $conversation->id,
            'sender_type' => $routeTo === 'bot' ? 'bot' : 'system',
            'sender_id' => null,
            'message' => $botReply,
        ]);

        if ($routeTo === 'hr') {
            $conversation->update([
                'status' => 'routed',
                'current_queue' => 'hr',
                'routed_to' => 'hr',
                'routed_at' => now(),
            ]);
        }

        return redirect()->route('client.support.index');
    }
}