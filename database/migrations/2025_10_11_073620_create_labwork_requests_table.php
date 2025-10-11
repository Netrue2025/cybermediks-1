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
        Schema::create('labwork_requests', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();

            // parties
            $table->foreignId('patient_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('labtech_id')->nullable()->constrained('users')->nullOnDelete();

            // request
            $table->string('lab_type', 200);
            $table->enum('collection_method', ['home', 'in_lab']);
            $table->string('address', 255)->nullable();        // when home
            $table->text('notes')->nullable();
            $table->timestamp('preferred_at')->nullable();

            // provider flow
            $table->enum('status', [
                'pending',          // patient submitted to selected provider
                'accepted',         // provider accepted
                'rejected',         // provider rejected (with reason)
                'scheduled',        // appointment scheduled
                'in_progress',      // sample collection / analysis ongoing
                'results_uploaded', // files uploaded (awaiting final completion)
                'completed',        // closed
                'cancelled',        // cancelled by patient/provider
            ])->default('pending');

            $table->timestamp('scheduled_at')->nullable();
            $table->decimal('price', 10, 2)->nullable();

            // results (single-file baseline; swap to a separate table later if you want multi-files)
            $table->string('results_path')->nullable();
            $table->string('results_original_name')->nullable();
            $table->string('results_mime')->nullable();
            $table->unsignedBigInteger('results_size')->nullable();
            $table->timestamp('results_uploaded_at')->nullable();
            $table->text('results_notes')->nullable();

            // misc
            $table->string('rejection_reason', 500)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('labwork_requests');
    }
};
