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
        Schema::create('keputusan', function (Blueprint $table) {
            $table->id('fld_keputusan_id');
            $table->string('fld_pel_nomat', 255);
            $table->decimal('fld_total_markah', 5, 2)->default(0);
            $table->string('fld_nilai_gred', 2)->nullable();
            $table->text('fld_nilai_komen')->nullable();
            $table->string('fld_sig_id', 255)->nullable();
            $table->timestamps();

            $table->foreign('fld_pel_nomat')->references('fld_pel_nomat')->on('pelajar')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keputusan');
    }
};
