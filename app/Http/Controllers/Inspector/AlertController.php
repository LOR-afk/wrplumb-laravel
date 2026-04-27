<?php

namespace App\Http\Controllers\Inspector;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AlertController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        $alerts = $user->alerts()->paginate(10);

        $user->alerts()->where('is_read', false)->update(['is_read' => true]);

        return view('inspector.alerts.index', compact('alerts'));
    }
}