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
        Schema::table('participants', function (Blueprint $table) {
            $table->string('nip')->nullable()->unique()->after('nik');
            $table->string('other_id')->nullable()->unique()->after('nip');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->string('location_name')->nullable()->after('longitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropColumn(['nip', 'other_id']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('location_name');
        });
    }
};
