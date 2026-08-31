<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlertController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $filter = $request->input('filter', 'all');

        if (!in_array($filter, ['all', 'unread', 'request', 'support', 'system'], true)) {
            $filter = 'all';
        }

        $baseQuery = UserAlert::where('user_id', $user->id);

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'unread' => (clone $baseQuery)->where('is_read', false)->count(),

            'requests' => (clone $baseQuery)->where(function ($q) {
                $q->where('title', 'like', '%request%')
                  ->orWhere('message', 'like', '%service request%')
                  ->orWhere('message', 'like', '%request%');
            })->count(),

            'support' => (clone $baseQuery)->where(function ($q) {
                $q->where('title', 'like', '%support%')
                  ->orWhere('message', 'like', '%support%')
                  ->orWhere('message', 'like', '%escalat%');
            })->count(),

            'system' => (clone $baseQuery)->where(function ($q) {
                $q->where('title', 'like', '%system%')
                  ->orWhere('title', 'like', '%archiv%')
                  ->orWhere('message', 'like', '%system%')
                  ->orWhere('message', 'like', '%archiv%');
            })->count(),
        ];

        $alerts = (clone $baseQuery)
            ->when($filter === 'unread', fn ($q) => $q->where('is_read', false))

            ->when($filter === 'request', function ($q) {
                $q->where(function ($sub) {
                    $sub->where('title', 'like', '%request%')
                        ->orWhere('message', 'like', '%service request%')
                        ->orWhere('message', 'like', '%request%');
                });
            })

            ->when($filter === 'support', function ($q) {
                $q->where(function ($sub) {
                    $sub->where('title', 'like', '%support%')
                        ->orWhere('message', 'like', '%support%')
                        ->orWhere('message', 'like', '%escalat%');
                });
            })

            ->when($filter === 'system', function ($q) {
                $q->where(function ($sub) {
                    $sub->where('title', 'like', '%system%')
                        ->orWhere('title', 'like', '%archiv%')
                        ->orWhere('message', 'like', '%system%')
                        ->orWhere('message', 'like', '%archiv%');
                });
            })

            ->latest()
            ->paginate(8)
            ->withQueryString();

        return view('admin.alerts.index', compact('alerts', 'stats', 'filter'));
    }

    public function markRead(UserAlert $alert)
    {
        abort_unless($alert->user_id === Auth::id(), 403);

        $alert->update(['is_read' => true]);

        return back()->with('success', 'Alert marked as read.');
    }

    public function markAllRead()
    {
        $user = Auth::user();

        UserAlert::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return back()->with('success', 'All alerts marked as read.');
    }

    public function clearRead()
    {
        $user = Auth::user();

        UserAlert::where('user_id', $user->id)
            ->where('is_read', true)
            ->delete();

        return back()->with('success', 'Read alerts cleared.');
    }
}