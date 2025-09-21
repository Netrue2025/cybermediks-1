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
        Schema::create('doctor_profiles', function (Blueprint $t) {
            $t->id();
            $t->foreignId('doctor_id')->unique()->constrained('users')->cascadeOnDelete();
            $t->string('title')->nullable();            // e.g., "Consultant Neurologist"
            $t->text('bio')->nullable();
            $t->boolean('is_available')->default(false);
            $t->decimal('consult_fee', 10, 2)->default(0); // base fee
            $t->unsignedInteger('avg_duration')->default(15); // minutes
            $t->decimal('lat', 10, 7)->nullable();
            $t->decimal('lng', 10, 7)->nullable();
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_profiles');
    }
};
