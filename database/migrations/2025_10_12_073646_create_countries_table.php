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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();

            // Essentials
            $table->string('name');                 // e.g. "Nigeria"
            $table->char('iso2', 2)->unique();      // e.g. "NG"
            $table->char('short', 3)->nullable()->index(); // optional alias (often same as iso2)

            // Minimal commerce/phone info
            $table->char('currency_code', 3)->nullable()->index(); // e.g. "NGN"
            $table->string('currency_symbol', 8)->nullable();      // e.g. "₦", "$", "€", "د.إ"
            $table->string('phone_code', 8)->nullable()->index();  // e.g. "234" (no "+")

            // Operational
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
