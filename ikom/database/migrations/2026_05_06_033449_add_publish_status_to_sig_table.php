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
            $table->tinyInteger('fld_publish_status')->default(0)->after('fld_sig_logo')->comment('0: Not Published, 1: Phase 1 (60%), 2: Phase 2 (100%)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sig', function (Blueprint $table) {
            $table->dropColumn('fld_publish_status');
        });
    }
};
