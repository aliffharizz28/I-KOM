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
        Schema::table('kehadiran', function (Blueprint $table) {
            $table->dropForeign(['fld_pel_nomat']);
            $table->dropColumn(['fld_hdr_peratusan', 'fld_pel_nomat']);

            $table->string('fld_sig_id')->nullable();
            $table->foreign('fld_sig_id')->references('fld_sig_id')->on('sig')->onDelete('cascade');

            $table->tinyInteger('fld_hdr_verified')->default(0)->comment('0 = Unverified, 1 = Verified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kehadiran', function (Blueprint $table) {
            $table->dropForeign(['fld_sig_id']);
            $table->dropColumn(['fld_sig_id', 'fld_hdr_verified']);

            $table->string('fld_hdr_peratusan')->nullable();
            $table->string('fld_pel_nomat')->nullable();
            $table->foreign('fld_pel_nomat')->references('fld_pel_nomat')->on('pelajar')->onDelete('cascade');
        });
    }
};
