<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;

class AdminTransactionsController extends Controller
{
    public function index(Request $r)
    {
        $type = $r->query('type'); // debit/credit
        $q    = trim((string)$r->query('q'));

        $tx = WalletTransaction::with('user')
            ->when($type, fn($w) => $w->where('type', $type))
            ->when($q !== '', function ($w) use ($q) {
                $w->where('reference', 'like', "%$q%")
                    ->orWhere('purpose', 'like', "%$q%")
                    ->orWhereHas('user', fn($u) => $u->where('first_name', 'like', "%$q%")->orWhere('last_name', 'like', "%$q%")->orWhere('email', 'like', "%$q%"));
            })
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.transactions.index', compact('tx', 'type', 'q'));
    }
}
