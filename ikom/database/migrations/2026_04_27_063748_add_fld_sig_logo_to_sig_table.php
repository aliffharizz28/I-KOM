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
        Schema::table('sig', function (Blueprint $table) {
            $table->string('fld_sig_logo')->nullable()->after('fld_sig_nama');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sig', function (Blueprint $table) {
            $table->dropColumn('fld_sig_logo');
        });
    }
};
