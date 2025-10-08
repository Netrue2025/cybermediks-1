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
        Schema::table('pharmacy_profiles', function (Blueprint $table) {
            $table->string('operating_license')->nullanle();
            $table->string('status')->default('pending');
            $table->string('rejection_reason')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pharmacy_profiles', function (Blueprint $table) {
            $table->dropColumn(['operating_license', 'status', 'rejection_reason']);
        });
    }
};
