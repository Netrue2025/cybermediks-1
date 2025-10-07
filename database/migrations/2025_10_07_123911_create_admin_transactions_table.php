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
        Schema::create('admin_transactions', function (Blueprint $t) {
            $t->id();
            $t->enum('type', ['credit', 'debit']);
            $t->decimal('amount', 10, 2);
            $t->string('currency', 3)->default('USD');
            $t->string('purpose')->nullable(); // "consultation", "topup", etc.
            $t->string('reference')->nullable()->index();
            $t->json('meta')->nullable();
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_transactions');
    }
};
