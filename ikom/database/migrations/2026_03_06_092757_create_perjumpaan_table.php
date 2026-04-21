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
        Schema::create('perjumpaan', function (Blueprint $table) {
            $table->id('fld_meet_id');
            $table->string('fld_meet_topik');
            $table->date('fld_meet_tarikh');
            $table->enum('fld_meet_status', [
                'Aktif',
                'Tidak Aktif'
                ])->default('Aktif');
            $table->string('fld_pel_nomat')->nullable(); // FK ke jadual Pelajar
            $table->foreign('fld_pel_nomat')
                ->references('fld_pel_nomat')
                ->on('pelajar')
                ->onDelete('set null');
            $table->string('fld_sig_id')->nullable(); // FK ke jadual SIG
            $table->foreign('fld_sig_id')
                ->references('fld_sig_id')
                ->on('sig')                
                ->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perjumpaan');
    }
};
