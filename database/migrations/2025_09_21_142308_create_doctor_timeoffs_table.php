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
        Schema::create('doctor_timeoffs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('doctor_id')->constrained('users')->cascadeOnDelete();
            $t->dateTime('start_at');
            $t->dateTime('end_at');
            $t->string('reason')->nullable();
            $t->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_timeoffs');
    }
};
