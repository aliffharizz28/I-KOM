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
        Schema::table('tugasan', function (Blueprint $table) {
            $table->string('fld_tgs_jenis', 20)->default('Individu')->after('fld_tgs_tarikh');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tugasan', function (Blueprint $table) {
            $table->dropColumn('fld_tgs_jenis');
        });
    }
};
