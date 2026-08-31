<?php
namespace App\Http\Controllers\Hr;

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

        if (!in_array($filter, [
            'all',
            'unread',
            'quotation',
            'payment',
            'contract',
            'support',
            'billing',
        ], true)) {
            $filter = 'all';
        }

        $baseQuery = UserAlert::query()
            ->where('user_id', $user->id);

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'unread' => (clone $baseQuery)
                ->where('is_read', false)
                ->count(),

            'quotation' => (clone $baseQuery)
                ->where(function ($query) {
                    $query->where('title', 'like', '%quotation%')
                        ->orWhere('message', 'like', '%quotation%')
                        ->orWhere('message', 'like', '%ready for quotation%');
                })
                ->count(),

            'payment' => (clone $baseQuery)
                ->where(function ($query) {
                    $query->where('title', 'like', '%payment%')
                        ->orWhere('message', 'like', '%payment%')
                        ->orWhere('message', 'like', '%receipt%');
                })
                ->count(),

            'contract' => (clone $baseQuery)
                ->where(function ($query) {
                    $query->where('title', 'like', '%contract%')
                        ->orWhere('message', 'like', '%contract%');
                })
                ->count(),

            'support' => (clone $baseQuery)
                ->where(function ($query) {
                    $query->where('title', 'like', '%support%')
                        ->orWhere('message', 'like', '%support%')
                        ->orWhere('message', 'like', '%escalat%');
                })
                ->count(),

            'billing' => (clone $baseQuery)
                ->where(function ($query) {
                    $query->where('title', 'like', '%invoice%')
                        ->orWhere('title', 'like', '%billing%')
                        ->orWhere('title', 'like', '%due%')
                        ->orWhere('message', 'like', '%invoice%')
                        ->orWhere('message', 'like', '%billing%')
                        ->orWhere('message', 'like', '%due date%')
                        ->orWhere('message', 'like', '%overdue%');
                })
                ->count(),
        ];

        $alerts = (clone $baseQuery)
            ->when($filter === 'unread', function ($query) {
                $query->where('is_read', false);
            })
            ->when($filter === 'quotation', function ($query) {
                $query->where(function ($subquery) {
                    $subquery->where('title', 'like', '%quotation%')
                        ->orWhere('message', 'like', '%quotation%')
                        ->orWhere('message', 'like', '%ready for quotation%');
                });
            })
            ->when($filter === 'payment', function ($query) {
                $query->where(function ($subquery) {
                    $subquery->where('title', 'like', '%payment%')
                        ->orWhere('message', 'like', '%payment%')
                        ->orWhere('message', 'like', '%receipt%');
                });
            })
            ->when($filter === 'contract', function ($query) {
                $query->where(function ($subquery) {
                    $subquery->where('title', 'like', '%contract%')
                        ->orWhere('message', 'like', '%contract%');
                });
            })
            ->when($filter === 'support', function ($query) {
                $query->where(function ($subquery) {
                    $subquery->where('title', 'like', '%support%')
                        ->orWhere('message', 'like', '%support%')
                        ->orWhere('message', 'like', '%escalat%');
                });
            })
            ->when($filter === 'billing', function ($query) {
                $query->where(function ($subquery) {
                    $subquery->where('title', 'like', '%invoice%')
                        ->orWhere('title', 'like', '%billing%')
                        ->orWhere('title', 'like', '%due%')
                        ->orWhere('message', 'like', '%invoice%')
                        ->orWhere('message', 'like', '%billing%')
                        ->orWhere('message', 'like', '%due date%')
                        ->orWhere('message', 'like', '%overdue%');
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('hr.alerts.index', compact(
            'alerts',
            'stats',
            'filter'
        ));
    }

    public function markRead(UserAlert $alert)
    {
        abort_unless($alert->user_id === Auth::id(), 403);

        $alert->update([
            'is_read' => true,
        ]);

        if ($alert->link) {
            return redirect($alert->link);
        }

        return back()->with('success', 'Alert marked as read.');
    }

    public function markAllRead()
    {
        UserAlert::query()
            ->where('user_id', Auth::id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
            ]);

        return back()->with(
            'success',
            'All HR alerts marked as read.'
        );
    }

    public function clearRead()
    {
        UserAlert::query()
            ->where('user_id', Auth::id())
            ->where('is_read', true)
            ->delete();

        return back()->with(
            'success',
            'Read HR alerts cleared.'
        );
    }
}