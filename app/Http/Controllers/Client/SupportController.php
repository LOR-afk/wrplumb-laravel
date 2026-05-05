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
            ->whereIn('status', ['open', 'routed', 'escalated'])
            ->latest()
            ->first();

        return view('client.support.index', compact('conversation'));
    }

    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        /*
         * IMPORTANT:
         * Do not use firstOrCreate with ['client_id' => ..., 'status' => 'open'].
         * Once a conversation becomes "routed", the next client message would create
         * a new conversation, making the previous chat look like it disappeared.
         */
        $conversation = SupportConversation::where('client_id', Auth::id())
            ->whereIn('status', ['open', 'routed', 'escalated'])
            ->latest()
            ->first();

        if (!$conversation) {
            $conversation = SupportConversation::create([
                'client_id' => Auth::id(),
                'status' => 'open',
                'current_queue' => 'bot',
                'routed_to' => null,
                'routed_at' => null,
                'resolved_at' => null,
            ]);
        }

        SupportMessage::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'client',
            'sender_id' => Auth::id(),
            'message' => $validated['message'],
        ]);

        /*
         * If the conversation is already handled by HR/Admin,
         * do not let the bot answer again. Just append the client's message
         * to the same conversation thread.
         */
        if (in_array($conversation->current_queue, ['hr', 'admin'], true)) {
            $conversation->update([
                'status' => $conversation->current_queue === 'admin' ? 'escalated' : 'routed',
                'current_queue' => $conversation->current_queue,
            ]);

            return $this->redirectBackToSupport($request);
        }

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
        } else {
            $conversation->update([
                'status' => 'open',
                'current_queue' => 'bot',
                'routed_to' => null,
                'routed_at' => null,
            ]);
        }

        return $this->redirectBackToSupport($request);
    }

    private function redirectBackToSupport(Request $request)
    {
        if ($request->boolean('support_widget')) {
            return redirect()->route('client.support.index', [
                'support_widget' => 1,
            ]);
        }

        return redirect()->route('client.support.index');
    }
}
