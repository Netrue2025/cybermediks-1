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
            if (!Schema::hasColumn('prescriptions', 'pharmacy_id')) {
                $t->foreignId('pharmacy_id')->nullable()->constrained('users')->nullOnDelete()->after('doctor_id');
            }
            if (!Schema::hasColumn('prescriptions', 'dispense_status')) {
                $t->enum('dispense_status', ['pending', 'ready', 'picked', 'cancelled'])
                    ->default('pending')->index()->after('status');
            }
            if (!Schema::hasColumn('prescriptions', 'total_amount')) {
                $t->decimal('total_amount', 10, 2)->nullable()->after('notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prescriptions', function (Blueprint $t) {
            if (Schema::hasColumn('prescriptions', 'total_amount')) $t->dropColumn('total_amount');
            if (Schema::hasColumn('prescriptions', 'dispense_status')) $t->dropColumn('dispense_status');
            if (Schema::hasColumn('prescriptions', 'pharmacy_id')) $t->dropConstrainedForeignId('pharmacy_id');
        });
    }
};
