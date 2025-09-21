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
        Schema::table('users', function (Blueprint $t) {
            $t->string('phone', 40)->nullable()->after('email');
            $t->string('gender', 10)->nullable()->after('phone');
            $t->date('dob')->nullable()->after('gender');
            $t->string('country', 100)->default('Nigeria')->after('dob');
            $t->string('address', 255)->nullable()->after('country');
            $t->decimal('wallet_balance', 10, 2)->default(0)->after('address');
        });
    }
    public function down(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->dropColumn(['phone', 'gender', 'dob', 'country', 'address', 'wallet_balance']);
        });
    }
};
