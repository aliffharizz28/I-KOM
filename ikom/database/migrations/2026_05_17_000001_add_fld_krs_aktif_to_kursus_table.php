<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kursus', function (Blueprint $table) {
            // Flag to mark which session is currently running
            $table->boolean('fld_krs_aktif')->default(false)->after('fld_krs_tahun');
        });
    }

    public function down(): void
    {
        Schema::table('kursus', function (Blueprint $table) {
            $table->dropColumn('fld_krs_aktif');
        });
    }
};
