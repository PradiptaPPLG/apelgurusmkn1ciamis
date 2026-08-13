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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apel_session_id')->constrained('apel_sessions')->onDelete('cascade');
            $table->string('participant_nik');
            $table->foreign('participant_nik')->references('nik')->on('participants')->onDelete('cascade');
            $table->mediumText('signature'); // Base64 signature image data URI
            $table->mediumText('photo')->nullable(); // Optional Base64 camera photo data URI
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamp('signed_in_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
