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
        Schema::create('pelajar', function (Blueprint $table) {
            $table->string('fld_pel_nomat')->primary();
            $table->enum('fld_pel_tahun', ['1', '2']);
            $table->enum('fld_pel_jurusan', ['Sains Komputer', 'Teknologi Maklumat', 'Kejuruteraan Perisian (Pembangunan Sistem Maklumat)', 'Kejuruteraan Perisian (Multimedia)']);
            $table->string('fld_pel_pic')->nullable();

            $table->string('fld_sig_id')->nullable(); // FK ke jadual SIG
            $table->foreign('fld_sig_id')
                ->references('fld_sig_id')
                ->on('sig')
                ->onDelete('set null');

            $table->unsignedBigInteger('fld_user_id')->nullable(); // FK ke jadual Pengguna
            $table->foreign('fld_user_id')
                ->references('fld_user_id')
                ->on('pengguna')
                ->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelajar');
    }
};
