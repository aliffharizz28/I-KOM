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
        Schema::create('penilaian', function (Blueprint $table) {
            $table->id('fld_nilai_id');
            $table->string('fld_nilai_markah');
            $table->string('fld_nilai_gred');
            $table->string('fld_nilai_komen')->nullable();
            $table->string('fld_sig_id')->nullable(); // FK ke jadual sig
            $table->foreign('fld_sig_id')
                ->references('fld_sig_id')
                ->on('sig')
                ->onDelete('set null');
            $table->string('fld_pel_nomat'); // FK ke jadual Pelajar
            $table->foreign('fld_pel_nomat')
                ->references('fld_pel_nomat')
                ->on('pelajar')
                ->onDelete('cascade');
            $table->unsignedBigInteger('fld_krit_id'); // FK ke jadual Kriteria
            $table->foreign('fld_krit_id')
                ->references('fld_krit_id')
                ->on('kriteria')
                ->onDelete('cascade');
            $table->unsignedBigInteger('fld_sub_id'); // FK ke jadual Subkriteria
            $table->foreign('fld_sub_id')
                ->references('fld_sub_id')
                ->on('subkriteria')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penilaian');
    }
};
