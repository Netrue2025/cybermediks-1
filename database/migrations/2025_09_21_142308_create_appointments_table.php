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
        Schema::create('appointments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('patient_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('doctor_id')->constrained('users')->cascadeOnDelete();
            $t->enum('type', ['video', 'chat', 'in_person'])->default('video');
            $t->timestamp('scheduled_at')->nullable(); // null for immediate chat queue
            $t->unsignedInteger('duration')->nullable(); // minutes
            $t->enum('status', ['pending', 'accepted', 'rejected', 'cancelled', 'completed'])->default('pending');
            $t->decimal('price', 10, 2)->default(0);
            $t->enum('payment_status', ['unpaid', 'paid', 'refunded'])->default('unpaid');
            $t->string('reason', 255)->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();

            $t->index(['doctor_id', 'scheduled_at']);
            $t->index(['patient_id', 'scheduled_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
