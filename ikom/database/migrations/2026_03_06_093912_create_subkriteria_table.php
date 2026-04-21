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
        Schema::create('subkriteria', function (Blueprint $table) {
            $table->id('fld_sub_id');
            $table->string('fld_sub_nama');
            $table->string('fld_sub_markah');
            $table->unsignedBigInteger('fld_krit_id'); // FK ke jadual kriteria
            $table->foreign('fld_krit_id')
                ->references('fld_krit_id')
                ->on('kriteria')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subkriteria');
    }
};
