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
        Schema::create('prescriptions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('patient_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('doctor_id')->constrained('users')->cascadeOnDelete();
            $t->enum('status', ['active', 'expired', 'cancelled'])->default('active');
            $t->unsignedSmallInteger('refills')->default(0);
            $t->text('notes')->nullable();
            $t->timestamps();
        });

        Schema::create('prescription_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('prescription_id')->constrained()->cascadeOnDelete();
            $t->string('drug');                      // name/strength
            $t->string('dose')->nullable();          // e.g. "1 tab"
            $t->string('frequency')->nullable();     // e.g. "2x/day"
            $t->unsignedSmallInteger('days')->nullable();
            $t->unsignedSmallInteger('quantity')->nullable();
            $t->text('directions')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('prescription_items');
        Schema::dropIfExists('prescriptions');
    }
};
