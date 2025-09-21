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
        Schema::create('doctor_credentials', function (Blueprint $t) {
            $t->id();
            $t->foreignId('doctor_id')->constrained('users')->cascadeOnDelete();
            $t->string('type'); // "Medical License", etc.
            $t->string('file_path');
            $t->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $t->timestamp('verified_at')->nullable();
            $t->text('review_notes')->nullable();
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_credentials');
    }
};
