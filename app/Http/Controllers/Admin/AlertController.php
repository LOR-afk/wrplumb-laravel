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

    public function open(UserAlert $alert)
    {
        abort_unless($alert->user_id === Auth::id(), 403);

        if (!$alert->is_read) {
            $alert->update(['is_read' => true]);
        }

        $parts = parse_url((string) $alert->link);
        $path = is_array($parts) ? ($parts['path'] ?? '') : '';

        if (!is_string($path) || !preg_match('#^/admin(?:/|$)#', $path)) {
            return redirect()->route('admin.alerts.index');
        }

        if ($path === '/admin/quotations') {
            parse_str((string) ($parts['query'] ?? ''), $parameters);

            if (empty($parameters['request_id'])) {
                $requestId = $this->resolveLegacyServiceRequest($alert);

                if ($requestId !== null) {
                    return redirect()->route('admin.quotations.index', [
                        'request_id' => $requestId,
                    ]);
                }
            }
        }

        $query = isset($parts['query']) ? '?' . $parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

        return redirect()->to($path . $query . $fragment);
    }

    private function resolveLegacyServiceRequest(UserAlert $alert): ?int
    {
        $message = trim((string) $alert->message);

        if (preg_match('/\bIR-\d{8}-\d{6}\b/i', $message, $match)) {
            $requestId = \App\Models\InspectionReport::query()
                ->where('report_no', $match[0])
                ->value('quotation_request_id');

            return $requestId ? (int) $requestId : null;
        }

        if (!preg_match('/^(.+?) submitted a new (?:service|quotation) request\.?$/iu', $message, $match)) {
            return null;
        }

        $targetName = mb_strtolower(trim($match[1]));
        $createdAt = $alert->created_at;

        if (!$createdAt || $targetName === '') {
            return null;
        }

        $matches = \App\Models\QuotationRequest::query()
            ->whereNull('archived_at')
            ->whereBetween('created_at', [
                $createdAt->copy()->subMinutes(15),
                $createdAt->copy()->addMinutes(5),
            ])
            ->get()
            ->filter(fn ($item) => mb_strtolower(trim((string) $item->full_name)) === $targetName)
            ->values();

        return $matches->count() === 1 ? (int) $matches->first()->id : null;
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
