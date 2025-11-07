<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WalletController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Balance = sum(credits) - sum(debits)
        $credits = WalletTransaction::forUser($user->id)->where('type', 'credit')->sum('amount');
        $debits  = WalletTransaction::forUser($user->id)->where('type', 'debit')->sum('amount');
        $balance = $user->wallet_balance;

        $transactions = WalletTransaction::with([])
            ->forUser($user->id)
            ->latestFirst()
            ->paginate(10)
            ->withQueryString();

        if ($request->ajax()) {
            return view('patient.wallet._list', compact('transactions'))->render();
        }

        return view('patient.wallet.index', compact('balance', 'transactions'));
    }

    public function withdraw(Request $request)
    {
        $data = $request->validate([
            'amount'   => 'required|numeric|min:5',
            'currency' => 'nullable|string|size:3'
        ]);

        $user = Auth::user();
        $currency = strtoupper($data['currency'] ?? 'USD');

        // Compute current balance to prevent overdraft
        $credits = WalletTransaction::forUser($user->id)->where('type', 'credit')->sum('amount');
        $debits  = WalletTransaction::forUser($user->id)->where('type', 'debit')->sum('amount');
        $balance = $user->wallet_balance;

        if ($data['amount'] > $balance) {
            return response()->json(['status' => 'error', 'message' => 'Insufficient balance'], 422);
        }

        // You might want a separate Withdrawals table; here we log as a debit with pending meta.
        $tx = WalletTransaction::create([
            'user_id'  => $user->id,
            'type'     => 'debit',
            'amount'   => $data['amount'],
            'currency' => $currency,
            'purpose'  => 'withdrawal_request',
            'reference' => 'TX-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(6)),
            'meta'     => ['status' => 'pending'],
        ]);

        $user->update(['wallet_balance' => $user->wallet_balance - $data['amount']]);

        return response()->json(['status' => 'success', 'message' => 'Withdrawal requested', 'tx' => $tx]);
    }

    public function startFlutterwave(Request $r)
    {
        try {
            $data = $r->validate([
                'amount'   => 'required|numeric|min:5',
            ]);
        } catch (\Throwable $e) {
            Log::warning('FW:init validation failed', [
                'user_id'  => Auth::id(),
                'errors'   => method_exists($e, 'errors') ? $e->errors() : $e->getMessage(),
                'payload'  => $r->only(['amount', 'currency']),
            ]);
            throw $e; // let Laravel return the validation response
        }

        $user     = Auth::user();
        $amount   = (float) $data['amount'];
        $currency = 'NGN';
        $txRef    = 'WALLET-' . Str::uuid()->toString();

        try {
            // Create pending local TX (idempotency anchor)
            WalletTransaction::create([
                'user_id'   => $user->id,
                'type'      => 'credit',
                'amount'    => $amount,
                'reference' => $txRef,
                'purpose'   => 'Wallet top-up (pending)',
                'meta'      => json_encode(['gateway' => 'flutterwave', 'currency' => $currency]),
                'status'    => 'pending',
            ]);
        } catch (\Throwable $e) {
            Log::error('FW:init could not create pending wallet transaction', [
                'user_id' => $user->id,
                'tx_ref'  => $txRef,
                'amount'  => $amount,
                'currency' => $currency,
                'error'   => $e->getMessage(),
            ]);
            return $this->fwInitFailResponse($r, 'Could not create transaction. Please try again.');
        }

        $payload = [
            'tx_ref'       => $txRef,
            'amount'       => number_format($amount, 2, '.', ''),
            'currency'     => $currency,
            'redirect_url' => route('patient.wallet.callback'),
            'customer'     => [
                'email' => $user->email,
                'name'  => trim($user->first_name . ' ' . $user->last_name),
            ],
            'customizations' => [
                'title' => config('app.name') . ' Wallet',
                'logo'  => asset('images/logo.webp'),
            ],
        ];

        try {
            $base = rtrim(config('services.flutterwave.base_url'), '/');

            $http = Http::withToken(config('services.flutterwave.secret'))
                ->acceptJson()
                ->timeout((int) (config('services.flutterwave.timeout', 20)))
                ->asJson();

            $res = $http->post("{$base}/payments", $payload);
            $json = $res->json();

            if (!$res->successful() || ($json['status'] ?? null) !== 'success' || empty($json['data']['link'])) {
                Log::error('FW:init API error/invalid response', [
                    'user_id'  => $user->id,
                    'tx_ref'   => $txRef,
                    'status'   => $json['status'] ?? null,
                    'http'     => $res->status(),
                    'body'     => $json,
                ]);
                return $this->fwInitFailResponse($r, $json['message'] ?? 'Failed to initialize payment.');
            }

            $link = $json['data']['link'];

            // Success path — log at info for traceability
            Log::info('FW:init success', [
                'user_id' => $user->id,
                'tx_ref'  => $txRef,
                'amount'  => $amount,
                'currency' => $currency,
                'link'    => '<<redacted>>',
            ]);

            if ($r->expectsJson() || $r->ajax()) {
                return response()->json([
                    'ok'     => true,
                    'url'    => $link,
                    'tx_ref' => $txRef,
                ]);
            }

            return redirect()->away($link);
        } catch (\Throwable $e) {
            Log::error('FW:init exception', [
                'user_id'  => $user->id,
                'tx_ref'   => $txRef,
                'amount'   => $amount,
                'currency' => $currency,
                'error'    => $e->getMessage(),
            ]);
            return $this->fwInitFailResponse($r, 'Payment initialization failed. Please try again.');
        }
    }

    /**
     * Normalize error responses (AJAX vs full page).
     */
    private function fwInitFailResponse(Request $r, string $msg)
    {
        if ($r->expectsJson() || $r->ajax()) {
            return response()->json(['ok' => false, 'message' => $msg], 422);
        }
        return back()->with('error', $msg);
    }

    public function flutterwaveCallback(Request $r)
    {
        $status = $r->query('status');        // 'successful' | 'cancelled' | 'failed'
        $txRef  = $r->query('tx_ref');        // our reference
        $id     = $r->query('transaction_id'); // flutterwave transaction id

        if (!$txRef || !$id) {
            return redirect()->route('patient.wallet.index')->with('error', 'Invalid callback.');
        }

        // Verify with Flutterwave
        $verify = Http::withToken(config('services.flutterwave.secret'))
            ->get(rtrim(config('services.flutterwave.base_url'), '/') . "/transactions/{$id}/verify")
            ->json();

        if (($verify['status'] ?? '') !== 'success') {
            return redirect()->route('patient.wallet.index')->with('error', 'Verification failed.');
        }

        $data = $verify['data'] ?? [];
        // Strong checks
        if (
            ($data['status'] ?? '') !== 'successful' ||
            ($data['tx_ref'] ?? '') !== $txRef
        ) {
            return redirect()->route('patient.wallet.index')->with('error', 'Payment not successful.');
        }

        $amount   = (float)($data['amount'] ?? 0);
        $currency = $data['currency'] ?? 'USD';
        $user     = Auth::user();

        // Idempotent credit (within a transaction)
        DB::transaction(function () use ($user, $txRef, $amount, $currency, $data) {
            // lock or re-fetch the transaction
            $tx = WalletTransaction::where('reference', $txRef)->lockForUpdate()->first();

            // If we didn’t create a pending Transaction earlier, create now
            if (!$tx) {
                $tx = new WalletTransaction([
                    'user_id'   => $user->id,
                    'type'      => 'credit',
                    'amount'    => $amount,
                    'reference' => $txRef,
                    'purpose'   => 'Wallet top-up',
                ]);
            }

            // If already completed, bail (prevents double credit)
            if (($tx->status ?? null) === 'successful') {
                return;
            }

            // Update wallet & transaction
            $user->wallet_balance = (float)($user->wallet_balance ?? 0) + $amount;
            $user->save();

            $tx->purpose = 'Wallet top-up';
            $tx->meta    = json_encode([
                'gateway'  => 'flutterwave',
                'currency' => $currency,
                'flw_id'   => $data['id'] ?? null,
                'charged_amount' => $data['charged_amount'] ?? null,
            ]);
            $tx->status  = 'successful';
            $tx->save();
        });

        return redirect()->route('patient.wallet.index')->with('success', 'Wallet funded successfully.');
    }
}
