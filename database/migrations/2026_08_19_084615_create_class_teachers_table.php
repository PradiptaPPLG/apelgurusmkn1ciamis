<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('class_name'); // e.g. "10 AKL 1"
            $table->integer('year')->default(2025);
            $table->timestamps();

            $table->unique(['employee_id', 'class_name', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_teachers');
    }
};