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
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('school_name')->default('SMKN 1 Ciamis');
            $table->string('app_name')->default('Asign');
            $table->string('school_address')->nullable()->default('Jl. Jend. Sudirman No. 269, Ciamis, Jawa Barat 46211');
            $table->string('default_pagi_start', 10)->default('06:20');
            $table->string('default_pagi_end', 10)->default('06:40');
            $table->string('default_sore_start', 10)->default('14:50');
            $table->string('default_sore_end', 10)->default('15:20');
            $table->integer('default_radius')->default(25);
            $table->string('kepsek_name')->default('Drs. H. Asep Gunawan, M.Pd.');
            $table->string('kepsek_nip')->default('19680512 199403 1 005');
            $table->string('kepsek_pangkat')->default('Pembina Utama Muda / IV c');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
