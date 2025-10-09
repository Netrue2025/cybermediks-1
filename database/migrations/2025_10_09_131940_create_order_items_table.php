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
        Schema::create('order_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->foreignId('prescription_item_id')->constrained()->cascadeOnDelete();

            // Snapshot fields (in case Rx changes later)
            $t->string('drug');
            $t->string('dose')->nullable();
            $t->string('frequency')->nullable();
            $t->integer('days')->nullable();
            $t->integer('quantity')->default(1);
            $t->text('directions')->nullable();

            // Pricing / lifecycle
            $t->decimal('unit_price', 12, 2)->nullable();
            $t->decimal('line_total', 12, 2)->nullable();
            $t->string('status')->default('pending'); // pending|quoted|patient_confirmed|purchased|cancelled|refunded
            $t->timestamp('purchased_at')->nullable();
            $t->timestamp('fulfilled_at')->nullable();

            $t->timestamps();
            $t->index(['order_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
