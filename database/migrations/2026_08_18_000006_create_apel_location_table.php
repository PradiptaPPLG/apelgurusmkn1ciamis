<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apel_location', function (Blueprint $table) {
            $table->id();
            $table->decimal('latitude', 10, 8)->nullable()->comment('Latitude titik apel');
            $table->decimal('longitude', 11, 8)->nullable()->comment('Longitude titik apel');
            $table->unsignedInteger('radius_meter')->default(10)->comment('Radius area apel dalam meter');
            $table->string('label')->nullable()->comment('Nama/label titik apel');
            $table->string('updated_by')->nullable()->comment('Admin yang terakhir update');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apel_location');
    }
};
