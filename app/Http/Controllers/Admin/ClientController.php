<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $clients = User::where('role', 'client')
            ->when($request->search, function ($query) use ($request) {
                $query->where('first_name', 'like', '%' . $request->search . '%')
                    ->orWhere('last_name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%')
                    ->orWhere('username', 'like', '%' . $request->search . '%');
            })
            ->latest()
            ->paginate(10);

        return view('admin.clients.index', compact('clients'));
    }

    public function toggleStatus(User $user)
    {
        abort_if($user->role !== 'client', 404);

        $user->update([
            'is_active' => !$user->is_active,
        ]);

        return back()->with('success', 'Client account status updated.');
    }
}