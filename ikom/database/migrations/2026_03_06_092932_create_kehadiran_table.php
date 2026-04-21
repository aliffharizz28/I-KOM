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
        Schema::create('kehadiran', function (Blueprint $table) {
            $table->id('fld_hdr_id');
            $table->string('fld_hdr_peratusan');
            $table->enum('fld_hdr_status', ['Hadir', 'Tidak Hadir']);
            $table->unsignedBigInteger('fld_meet_id'); // FK ke jadual Perjumpaan
            $table->foreign('fld_meet_id')
                ->references('fld_meet_id')
                ->on('perjumpaan')
                ->onDelete('cascade');
            $table->string('fld_pel_nomat'); // FK ke jadual Pelajar
            $table->foreign('fld_pel_nomat')
                ->references('fld_pel_nomat')
                ->on('pelajar')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kehadiran');
    }
};
