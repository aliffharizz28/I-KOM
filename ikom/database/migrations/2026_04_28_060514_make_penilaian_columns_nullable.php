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
        Schema::table('penilaian', function (Blueprint $table) {
            $table->unsignedBigInteger('fld_krit_id')->nullable()->change();
            $table->unsignedBigInteger('fld_sub_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penilaian', function (Blueprint $table) {
            $table->unsignedBigInteger('fld_krit_id')->nullable(false)->change();
            $table->unsignedBigInteger('fld_sub_id')->nullable(false)->change();
        });
    }
};
