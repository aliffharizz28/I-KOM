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
            $table->dropForeign(['fld_sub_id']);
            $table->dropForeign(['fld_desc_id']);
            $table->dropColumn(['fld_sub_id', 'fld_desc_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penilaian', function (Blueprint $table) {
            $table->unsignedBigInteger('fld_sub_id')->nullable();
            $table->unsignedBigInteger('fld_desc_id')->nullable();

            $table->foreign('fld_sub_id')
                ->references('fld_sub_id')
                ->on('subkriteria')
                ->onDelete('cascade');
                
            $table->foreign('fld_desc_id')
                ->references('fld_desc_id')
                ->on('subkriteria_desc')
                ->onDelete('cascade');
        });
    }
};
