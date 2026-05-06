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
        Schema::table('perjumpaan', function (Blueprint $table) {
            $table->dropForeign(['fld_pel_nomat']);
            $table->dropColumn(['fld_meet_status', 'fld_pel_nomat']);
            $table->tinyInteger('fld_meet_verify')->default(0)->comment('0 = Unverified, 1 = Verified');
        });

        Schema::table('kehadiran', function (Blueprint $table) {
            $table->dropForeign(['fld_sig_id']);
            $table->dropColumn(['fld_sig_id', 'fld_hdr_verified']);
            $table->string('fld_pel_nomat')->nullable();
            $table->foreign('fld_pel_nomat')->references('fld_pel_nomat')->on('pelajar')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perjumpaan', function (Blueprint $table) {
            $table->dropColumn('fld_meet_verify');
            $table->enum('fld_meet_status', ['Aktif', 'Tidak Aktif'])->default('Aktif');
            $table->string('fld_pel_nomat')->nullable();
            $table->foreign('fld_pel_nomat')->references('fld_pel_nomat')->on('pelajar')->onDelete('set null');
        });

        Schema::table('kehadiran', function (Blueprint $table) {
            $table->dropForeign(['fld_pel_nomat']);
            $table->dropColumn('fld_pel_nomat');
            $table->string('fld_sig_id')->nullable();
            $table->foreign('fld_sig_id')->references('fld_sig_id')->on('sig')->onDelete('cascade');
            $table->tinyInteger('fld_hdr_verified')->default(0);
        });
    }
};
