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
        Schema::create('kursus', function (Blueprint $table) {
            $table->id('fld_krs_id');
            $table->enum('fld_krs_nama', ['Inovasi Digital', 'Komuniti Digital']);
            $table->enum('fld_krs_semester', ['Semester 1', 'Semester 2']);
            $table->enum('fld_krs_tahun', ['2025/2026', '2026/2027', '2027/2028', '2028/2029', '2029/2030', '2030/2031', '2031/2032', '2032/2033', '2033/2034', '2034/2035', '2035/2036',]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kursus');
    }
};
