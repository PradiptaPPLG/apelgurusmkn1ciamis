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
        Schema::table('apel_sessions', function (Blueprint $table) {
            // Berapa hari sesi berlaku (1 = hanya hari itu, 7 = seminggu)
            $table->unsignedTinyInteger('valid_days')->default(1)->after('code')
                  ->comment('Jumlah hari sesi berlaku terhitung dari kolom date');

            // Tanggal berakhir = date + valid_days - 1
            $table->date('end_date')->nullable()->after('valid_days')
                  ->comment('Tanggal terakhir sesi dapat digunakan untuk absen');
        });

        // Isi end_date untuk baris lama (retroactive)
        \DB::statement('UPDATE apel_sessions SET end_date = date WHERE end_date IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('apel_sessions', function (Blueprint $table) {
            $table->dropColumn(['valid_days', 'end_date']);
        });
    }
};
