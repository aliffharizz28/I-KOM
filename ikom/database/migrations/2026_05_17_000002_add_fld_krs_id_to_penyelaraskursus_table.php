<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penyelaraskursus', function (Blueprint $table) {
            // Link the coordinator to the specific course session they activated
            $table->unsignedBigInteger('fld_krs_id')->nullable()->after('fld_user_id');
            $table->foreign('fld_krs_id')
                ->references('fld_krs_id')
                ->on('kursus')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('penyelaraskursus', function (Blueprint $table) {
            $table->dropForeign(['fld_krs_id']);
            $table->dropColumn('fld_krs_id');
        });
    }
};
