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
        Schema::table('prescriptions', function (Blueprint $t) {
            if (!Schema::hasColumn('prescriptions', 'code'))   $t->string('code')->unique()->after('doctor_id');
            if (!Schema::hasColumn('prescriptions', 'status')) $t->enum('status', ['active', 'expired', 'refill_requested'])->default('active')->index()->after('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            if (Schema::hasColumn('prescriptions', 'code'))   $table->dropColumn('code');
            if (Schema::hasColumn('prescriptions', 'status')) $table->dropColumn('status');
        });
    }
};
