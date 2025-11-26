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
        // Update all existing withdrawal_requests from USD to NGN
        DB::table('withdrawal_requests')
            ->where('currency', 'USD')
            ->update(['currency' => 'NGN']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to USD if needed
        DB::table('withdrawal_requests')
            ->where('currency', 'NGN')
            ->update(['currency' => 'USD']);
    }
};
