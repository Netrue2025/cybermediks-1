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
        Schema::table('users', function (Blueprint $t) {
            // generic location fields usable by patients, doctors, pharmacies
            $t->decimal('lat', 10, 7)->nullable()->after('address');
            $t->decimal('lng', 10, 7)->nullable()->after('lat');
            $t->index(['role']);
            $t->index(['lat', 'lng']); // helps proximity queries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->dropIndex(['users_role_index']);
            $t->dropIndex(['users_lat_lng_index']);
            $t->dropColumn(['lat', 'lng']);
        });
    }
};
