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
        Schema::create('tugasan', function (Blueprint $table) {
            $table->id('fld_tgs_id');
            $table->string('fld_tgs_nama');
            $table->text('fld_tgs_desc');
            $table->date('fld_tgs_tarikh');
            $table->string('fld_tgs_file')->nullable();
            $table->enum('fld_tgs_status', ['Aktif', 'Tidak Aktif'])->default('Aktif');
            $table->string('fld_sig_id')->nullable(); // FK ke jadual sig
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
        Schema::dropIfExists('tugasan');
    }
};
