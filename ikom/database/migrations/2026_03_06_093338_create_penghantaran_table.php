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
        Schema::create('penghantaran', function (Blueprint $table) {
            $table->string('fld_pgh_id')->primary();
            $table->string('fld_pgh_fail');
            $table->string('fld_pel_nomat'); // FK ke jadual Pelajar
            $table->foreign('fld_pel_nomat')
                ->references('fld_pel_nomat')
                ->on('pelajar')
                ->onDelete('cascade');
            $table->unsignedBigInteger('fld_tgs_id'); // FK ke jadual Tugasan
            $table->foreign('fld_tgs_id')
                ->references('fld_tgs_id')
                ->on('tugasan')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penghantaran');
    }
};
