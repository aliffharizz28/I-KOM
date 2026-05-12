<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Stores the SIG-specific subkriteria allocation.
     * Each SIG coordinator can independently assign different subkriteria
     * to each kriteria with their own mark weights.
     */
    public function up(): void
    {
        Schema::create('sig_subkriteria', function (Blueprint $table) {
            $table->id('fld_sigsub_id');

            // Which SIG this allocation belongs to
            $table->string('fld_sig_id');
            $table->foreign('fld_sig_id')
                ->references('fld_sig_id')
                ->on('sig')
                ->onDelete('cascade');

            // Which kriteria this subkriteria is assigned under
            $table->unsignedBigInteger('fld_krit_id');
            $table->foreign('fld_krit_id')
                ->references('fld_krit_id')
                ->on('kriteria')
                ->onDelete('cascade');

            // Which subkriteria is assigned
            $table->unsignedBigInteger('fld_sub_id');
            $table->foreign('fld_sub_id')
                ->references('fld_sub_id')
                ->on('subkriteria')
                ->onDelete('cascade');

            // Mark weight for this subkriteria within this SIG's kriteria
            $table->decimal('fld_sub_markah', 5, 2)->default(0);

            $table->timestamps();

            // A SIG can only assign a subkriteria once per kriteria
            $table->unique(['fld_sig_id', 'fld_krit_id', 'fld_sub_id'], 'unique_sig_krit_sub');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sig_subkriteria');
    }
};
