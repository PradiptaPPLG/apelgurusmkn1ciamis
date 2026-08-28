<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds a device_uuid column to the attendances table.
     * A unique constraint on (apel_session_id, device_uuid) ensures that
     * one physical device (identified by a UUID stored in localStorage)
     * can only submit one attendance record per session — preventing proxy
     * attendance (titip absen) where person B uses their own device to
     * check in on behalf of person A.
     *
     * The column is nullable so existing attendance records are not affected.
     * NULL values are not considered duplicates by the database engine,
     * so the unique constraint only applies to non-null device UUIDs.
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('device_uuid', 36)->nullable()->after('location_name');

            // One device per session — prevents proxy attendance
            $table->unique(['apel_session_id', 'device_uuid'], 'unique_session_device');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique('unique_session_device');
            $table->dropColumn('device_uuid');
        });
    }
};
