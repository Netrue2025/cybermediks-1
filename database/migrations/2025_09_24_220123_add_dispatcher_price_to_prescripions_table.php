<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            // Delivery fee set by dispatcher
            $table->decimal('dispatcher_price', 10, 2)->nullable()->after('total_amount');
            $table->string('dispense_status')->change();
        });
    }

    public function down(): void
    {
        // Rollback enum change first if you modified it
        DB::statement("
            ALTER TABLE prescriptions 
            MODIFY COLUMN dispense_status 
            ENUM(
              'pending',
              'price_assigned',
              'price_confirmed',
              'ready',
              'picked',
              'cancelled'
            ) DEFAULT 'pending'
        ");

        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropColumn('dispatcher_price');
        });
    }
};
