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
        Schema::create('appointment_disputes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $t->foreignId('patient_id')->constrained('users')->cascadeOnDelete();
            $t->text('reason');
            $t->string('status')->default('open'); // open, admin_review, resolved
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointment_disputes');
    }
};
