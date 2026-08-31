<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserAlert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AlertController extends Controller
{
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();

        $alerts = $user->alerts()
            ->latest()
            ->paginate(10);

        return view('client.alerts.index', compact('alerts'));
    }

    public function markRead(
        Request $request,
        UserAlert $alert
    ): RedirectResponse {
        abort_unless(
            (int) $alert->user_id === (int) $request->user()->id,
            403
        );

        if (!$alert->is_read) {
            $alert->update([
                'is_read' => true,
            ]);
        }

        if ($alert->link) {
            return redirect()->to($alert->link);
        }

        return redirect()->route('client.alerts.index');
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()
            ->alerts()
            ->where('is_read', false)
            ->update([
                'is_read' => true,
            ]);

        return back()->with(
            'success',
            'All notifications have been marked as read.'
        );
    }
}