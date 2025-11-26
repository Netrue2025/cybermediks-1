<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
         Schema::create('withdrawal_requests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->decimal('amount', 12, 2);
            $t->string('currency', 10)->default('NGN');
            $t->string('status', 30)->default('pending'); // pending, approved, rejected, paid, failed
            $t->string('reference')->unique();           // our idempotency ref
            $t->string('payout_channel')->default('flutterwave');

            // Payout details (NGN bank transfer via Flutterwave)
            $t->string('bank_name')->nullable();
            $t->string('bank_code')->nullable();         // flutterwave account_bank
            $t->string('account_number')->nullable();
            $t->string('account_name')->nullable();
            $t->string('routing_number')->nullable();    // optional
            $t->string('swift_code')->nullable();        // optional
            $t->json('meta')->nullable();                // anything else

            // Audit
            $t->timestamp('approved_at')->nullable();
            $t->timestamp('paid_at')->nullable();
            $t->timestamp('rejected_at')->nullable();
            $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();

            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawal_requests');
    }
};
