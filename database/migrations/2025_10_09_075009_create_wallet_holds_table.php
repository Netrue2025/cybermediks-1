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
        Schema::create('wallet_holds', function (Blueprint $t) {
            $t->id();
            $t->foreignId('source_user_id')->constrained('users')->cascadeOnDelete(); // who we took the money from (doctor)
            $t->foreignId('target_user_id')->constrained('users')->cascadeOnDelete(); // the counterparty (patient)
            $t->decimal('amount', 12, 2);
            $t->string('status')->default('pending'); // pending|released_to_patient|released_to_doctor|partial
            $t->string('ref_type'); // 'appointment'
            $t->unsignedBigInteger('ref_id'); // appointment id
            $t->json('meta')->nullable();     // dispute_id, notes, etc
            $t->timestamps();
            $t->index(['ref_type', 'ref_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_holds');
    }
};
