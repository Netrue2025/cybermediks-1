<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function revenue(Request $r)
    {
        $role   = $r->query('role');              // 'patient','doctor','pharmacy','dispatcher','labtech' (optional)
        $from   = $r->query('from');              // YYYY-MM-DD (optional)
        $to     = $r->query('to');                // YYYY-MM-DD (optional)
        $type   = $r->query('type');              // 'credit'|'debit' (optional filter on tx type)
        $q      = trim((string) $r->query('q', '')); // optional free-text on country name or ISO2
        $sort   = $r->query('sort', 'net_desc');  // default sort

        $allowedRoles = ['patient', 'doctor', 'pharmacy', 'dispatcher', 'labtech', 'admin'];
        if ($role && !in_array($role, $allowedRoles, true)) {
            $role = null;
        }

        $rows = DB::table('wallet_transactions as t')
            ->join('users as u', 'u.id', '=', 't.user_id')
            ->leftJoin('countries as c', 'c.id', '=', 'u.country_id')
            ->when($role, fn($q1) => $q1->where('u.role', $role))
            ->when($from, fn($q1) => $q1->whereDate('t.created_at', '>=', $from))
            ->when($to,   fn($q1) => $q1->whereDate('t.created_at', '<=', $to))
            ->when($type, fn($q1) => $q1->where('t.type', $type))
            ->when($q !== '', function ($q1) use ($q) {
                $needle = strtolower($q);
                $q1->where(function ($qq) use ($needle) {
                    $qq->whereRaw('LOWER(COALESCE(c.name, "Unknown")) LIKE ?', ["%{$needle}%"])
                        ->orWhereRaw('LOWER(COALESCE(c.iso2, "")) = ?', [$needle]);
                });
            })
            ->selectRaw("
            u.role as role,
            COALESCE(c.name, 'Unknown') as country,
            COALESCE(c.iso2, '--')      as iso2,
            COALESCE(c.id,   0)         as country_id,
            COUNT(*) as tx_count,
            SUM(CASE WHEN t.type = 'credit' THEN t.amount ELSE 0 END) as credits,
            SUM(CASE WHEN t.type = 'debit'  THEN t.amount ELSE 0 END) as debits,
            SUM(CASE WHEN t.type = 'credit' THEN  t.amount ELSE -t.amount END) as net,
            MAX(t.created_at) as last_tx_at
        ")
            ->groupBy('u.role', 'c.id', 'c.name', 'c.iso2');

        // Sorting
        $sortMap = [
            'country_asc'  => ['country', 'asc'],
            'country_desc' => ['country', 'desc'],
            'role_asc'     => ['role', 'asc'],
            'role_desc'    => ['role', 'desc'],
            'credits_desc' => ['credits', 'desc'],
            'debits_desc'  => ['debits', 'desc'],
            'net_asc'      => ['net', 'asc'],
            'net_desc'     => ['net', 'desc'],
            'count_desc'   => ['tx_count', 'desc'],
            'last_desc'    => ['last_tx_at', 'desc'],
        ];
        [$col, $dir] = $sortMap[$sort] ?? ['net', 'desc'];

        $rows = $rows->orderBy($col, $dir)->paginate(30)->appends($r->query());

        // Totals (for the current filtered set)
        $totals = DB::table('wallet_transactions as t')
            ->join('users as u', 'u.id', '=', 't.user_id')
            ->leftJoin('countries as c', 'c.id', '=', 'u.country_id')
            ->when($role, fn($q1) => $q1->where('u.role', $role))
            ->when($from, fn($q1) => $q1->whereDate('t.created_at', '>=', $from))
            ->when($to,   fn($q1) => $q1->whereDate('t.created_at', '<=', $to))
            ->when($type, fn($q1) => $q1->where('t.type', $type))
            ->when($q !== '', function ($q1) use ($q) {
                $needle = strtolower($q);
                $q1->where(function ($qq) use ($needle) {
                    $qq->whereRaw('LOWER(COALESCE(c.name, "Unknown")) LIKE ?', ["%{$needle}%"])
                        ->orWhereRaw('LOWER(COALESCE(c.iso2, "")) = ?', [$needle]);
                });
            })
            ->selectRaw("
            SUM(CASE WHEN t.type = 'credit' THEN t.amount ELSE 0 END) as credits,
            SUM(CASE WHEN t.type = 'debit'  THEN t.amount ELSE 0 END) as debits,
            SUM(CASE WHEN t.type = 'credit' THEN  t.amount ELSE -t.amount END) as net
        ")
            ->first();

        return view('admin.transactions.revenue', [
            'rows'   => $rows,
            'totals' => $totals,
            'role'   => $role,
            'from'   => $from,
            'to'     => $to,
            'type'   => $type,
            'q'      => $q,
            'sort'   => $sort,
        ]);
    }
}
