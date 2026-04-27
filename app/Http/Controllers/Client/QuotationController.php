<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use Illuminate\Support\Facades\Auth;

class QuotationController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $quotations = Quotation::with(['request', 'items'])
            ->whereHas('request', function ($query) use ($user) {
                $query->where('email', $user->email);
            })
            ->latest()
            ->paginate(10);

        return view('client.quotations.index', compact('quotations'));
    }

    public function show(Quotation $quotation)
    {
        $user = Auth::user();

        $quotation->load(['request', 'items', 'preparedBy']);

        abort_if($quotation->request->email !== $user->email, 403);

        return view('client.quotations.show', compact('quotation'));
    }
}