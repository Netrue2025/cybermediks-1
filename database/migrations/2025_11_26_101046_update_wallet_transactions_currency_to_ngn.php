<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update all existing wallet_transactions from USD to NGN
        DB::table('wallet_transactions')
            ->where('currency', 'USD')
            ->update(['currency' => 'NGN']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to USD if needed
        DB::table('wallet_transactions')
            ->where('currency', 'NGN')
            ->update(['currency' => 'USD']);
    }
};
