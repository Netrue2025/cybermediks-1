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
        Schema::create('specialties', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->string('icon')->nullable();
            $t->string('color')->nullable();
            $t->string('slug')->unique();
            $t->timestamps();
        });

        Schema::create('doctor_specialty', function (Blueprint $t) {
            $t->id();
            $t->foreignId('doctor_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('specialty_id')->constrained()->cascadeOnDelete();
            $t->unique(['doctor_id', 'specialty_id']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('doctor_specialty');
        Schema::dropIfExists('specialties');
    }
};
