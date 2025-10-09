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
        Schema::table('orders', function (Blueprint $table) {
            // core status fields (if you don't already have them)
            if (!Schema::hasColumn('orders', 'status')) {
                $table->string('status', 50)->default('pending')->index();
            }

            if (!Schema::hasColumn('orders', 'items_subtotal')) {
                $table->decimal('items_subtotal', 12, 2)->nullable();
            }

            // dispatcher-related
            if (!Schema::hasColumn('orders', 'dispatcher_id')) {
                $table->unsignedBigInteger('dispatcher_id')->nullable()->after('pharmacy_id')->index();
                // optional FK; comment out if you prefer no constraints:
                $table->foreign('dispatcher_id')->references('id')->on('users')->nullOnDelete();
            }

            if (!Schema::hasColumn('orders', 'dispatcher_price')) {
                $table->decimal('dispatcher_price', 12, 2)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'dispatcher_price')) {
                $table->dropColumn('dispatcher_price');
            }
            if (Schema::hasColumn('orders', 'dispatcher_id')) {
                // drop FK first if created
                try {
                    $table->dropForeign(['dispatcher_id']);
                } catch (\Throwable $e) {
                }
                $table->dropColumn('dispatcher_id');
            }
            if (Schema::hasColumn('orders', 'items_subtotal')) {
                $table->dropColumn('items_subtotal');
            }
            // keep status if other parts depend on it; drop only if you created it here
            // $table->dropColumn('status');
        });
    }
};
