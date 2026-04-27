<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\SupportConversation;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $conversation = SupportConversation::where('client_id', Auth::id())
            ->whereIn('status', ['open', 'routed'])
            ->latest()
            ->first();

        if (!$conversation) {
            $conversation = SupportConversation::create([
                'client_id' => Auth::id(),
                'status' => 'open',
                'current_queue' => 'bot',
            ]);
        }

        $conversation->load('messages');

        return view('client.dashboard', [
            'user' => Auth::user(),
            'conversation' => $conversation,
        ]);
    }
}