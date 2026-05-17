<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pendaftaran_pelajar', function (Blueprint $table) {
            $table->id('fld_daftar_id');

            // Student permanent profile
            $table->string('fld_pel_nomat');
            $table->foreign('fld_pel_nomat')
                ->references('fld_pel_nomat')
                ->on('pelajar')
                ->onDelete('cascade');

            // Which course session this enrollment belongs to
            $table->unsignedBigInteger('fld_krs_id');
            $table->foreign('fld_krs_id')
                ->references('fld_krs_id')
                ->on('kursus')
                ->onDelete('cascade');

            // Which SIG the student joined for this session
            $table->string('fld_sig_id');
            $table->foreign('fld_sig_id')
                ->references('fld_sig_id')
                ->on('sig')
                ->onDelete('cascade');

            // Prevent duplicate enrollment of same student in same session
            $table->unique(['fld_pel_nomat', 'fld_krs_id'], 'unique_student_session');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendaftaran_pelajar');
    }
};
