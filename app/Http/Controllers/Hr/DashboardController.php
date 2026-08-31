<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\SupportConversation;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $hrQueue = SupportConversation::query()
            ->where('current_queue', 'hr');

        $openCount = (clone $hrQueue)->count();

        $routedCount = (clone $hrQueue)
            ->where('status', 'routed')
            ->count();

        $resolvedCount = SupportConversation::query()
            ->where('status', 'resolved')
            ->count();

        $pendingFollowups = (clone $hrQueue)
            ->whereIn('status', ['open', 'routed', 'pending'])
            ->count();

        $receivedCount = SupportConversation::query()->count();
        $totalForPercent = max($receivedCount, 1);

        $recentConversations = SupportConversation::query()
            ->with('client')
            ->when(Schema::hasColumn('support_conversations', 'current_queue'), function ($query) {
                $query->where(function ($q) {
                    $q->where('current_queue', 'hr')
                        ->orWhere('status', 'resolved');
                });
            })
            ->latest('updated_at')
            ->take(4)
            ->get()
            ->map(function ($conversation) {
                $conversation->latest_message = 'Support conversation updated';
                return $conversation;
            });

        return view('hr.dashboard', [
            'openCount' => $openCount,
            'routedCount' => $routedCount,
            'resolvedCount' => $resolvedCount,
            'pendingFollowups' => $pendingFollowups,
            'receivedCount' => $receivedCount,


            'openPercent' => max(12, min(100, round(($openCount / $totalForPercent) * 100))),
            'routedPercent' => max(25, min(100, round(($routedCount / $totalForPercent) * 100))),
            'resolvedPercent' => max(75, min(100, round(($resolvedCount / $totalForPercent) * 100))),
            'pendingPercent' => max(40, min(100, round(($pendingFollowups / $totalForPercent) * 100))),

            'averageResponseTime' => '1h 24m',
            'slaCompliance' => '96%',
            'recentConversations' => $recentConversations,
        ]);
    }
}
