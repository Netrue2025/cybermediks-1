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
        Schema::create('doctor_schedules', function (Blueprint $t) {
            $t->id();
            $t->foreignId('doctor_id')->constrained('users')->cascadeOnDelete();
            $t->unsignedTinyInteger('weekday'); // 1=Mon … 7=Sun
            $t->time('start_time');
            $t->time('end_time');
            $t->boolean('enabled')->default(true);
            $t->timestamps();
            $t->unique(['doctor_id', 'weekday', 'start_time', 'end_time']);
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_schedules');
    }
};
