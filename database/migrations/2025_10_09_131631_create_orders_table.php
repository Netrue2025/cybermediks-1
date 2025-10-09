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
        Schema::create('orders', function (Blueprint $t) {
            $t->id();
            $t->foreignId('prescription_id')->constrained()->cascadeOnDelete();
            $t->foreignId('patient_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('pharmacy_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('status')->default('pending'); // pending|quoted|patient_confirmed|paid|dispensing|ready|dispatched|delivered|cancelled|refunded|partial
            $t->decimal('items_subtotal', 12, 2)->nullable();
            $t->decimal('delivery_fee', 12, 2)->nullable();
            $t->decimal('grand_total', 12, 2)->nullable();
            $t->string('currency', 3)->default('USD');
            $t->json('meta')->nullable(); // dispatcher refs, etc
            $t->timestamps();
            $t->index(['patient_id', 'pharmacy_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
