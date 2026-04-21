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
        Schema::create('majlistertinggi', function (Blueprint $table) {
            $table->id('fld_mt_id');
            $table->enum('fld_mt_jawatan', ['Pengerusi', 'Timbalan Pengerusi', 'Setiausaha', 'Bendahari',]);
            $table->string('fld_pel_nomat')->nullable(); // FK ke jadual pelajar
            $table->foreign('fld_pel_nomat')
                ->references('fld_pel_nomat')
                ->on('pelajar')
                ->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('majlistertinggi');
    }
};
