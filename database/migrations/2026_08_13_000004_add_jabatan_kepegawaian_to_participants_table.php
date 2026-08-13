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
            // Jabatan: functional title (e.g. Guru Mata Pelajaran, Kepala Sekolah, Staf TU)
            $table->string('jabatan')->nullable()->after('name');
            // Jenis Kepegawaian: employment type (ASN/PNS/P3K/Honorer/Mahasiswa)
            $table->string('jenis_kepegawaian')->nullable()->after('jabatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropColumn(['jabatan', 'jenis_kepegawaian']);
        });
    }
};
