<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FlutterwaveWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Verify signature (Flutterwave sends header: verif-hash)
        $signature = $request->header('verif-hash');
        $expected  = config('services.flutterwave.webhook.hash');

        if (!$signature || $signature !== $expected) {
            return response('Invalid signature', 401);
        }

        $payload = $request->all();
        $event   = $payload['event'] ?? '';
        $data    = $payload['data'] ?? [];

        if ($event !== 'charge.completed') {
            return response('Ignored', 200);
        }

        if (($data['status'] ?? '') !== 'successful') {
            return response('Not successful', 200);
        }

        $txRef  = $data['tx_ref'] ?? null;
        $amount = (float) ($data['amount'] ?? 0);

        if (!$txRef || $amount <= 0) {
            return response('Bad data', 200);
        }

        DB::transaction(function () use ($txRef, $amount, $data) {
            $tx = WalletTransaction::where('reference', $txRef)->lockForUpdate()->first();
            if (!$tx) return;                         // only reconcile known refs
            if (($tx->status ?? null) === 'successful') return;

            $user = User::find($tx->user_id);
            if (!$user) return;

            $user->wallet_balance = (float)($user->wallet_balance ?? 0) + $amount;
            $user->save();

            $existingMeta = (array) json_decode($tx->meta ?? '[]', true);

            $tx->status = 'successful';
            $tx->meta   = json_encode(array_merge($existingMeta, [
                'webhook'        => true,
                'flw_id'         => $data['id'] ?? null,
                'currency'       => $data['currency'] ?? null,
                'charged_amount' => $data['charged_amount'] ?? null,
            ]));
            $tx->save();
        });

        return response('OK', 200);
    }
}
