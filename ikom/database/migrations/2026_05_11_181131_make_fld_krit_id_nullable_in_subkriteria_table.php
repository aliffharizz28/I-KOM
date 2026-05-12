<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Makes fld_krit_id and fld_sub_markah nullable so that a subkriteria
     * can be "unlinked" from a kriteria when it is removed during editing.
     */
    public function up(): void
    {
        Schema::table('subkriteria', function (Blueprint $table) {
            // Drop the existing FK before altering the column
            $table->dropForeign(['fld_krit_id']);

            // Make fld_krit_id nullable (unlinked subkriteria will have null)
            $table->unsignedBigInteger('fld_krit_id')->nullable()->change();

            // Make fld_sub_markah nullable as well
            $table->string('fld_sub_markah')->nullable()->change();

            // Re-add FK with nullable support
            $table->foreign('fld_krit_id')
                ->references('fld_krit_id')
                ->on('kriteria')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subkriteria', function (Blueprint $table) {
            $table->dropForeign(['fld_krit_id']);
            $table->unsignedBigInteger('fld_krit_id')->nullable(false)->change();
            $table->string('fld_sub_markah')->nullable(false)->change();
            $table->foreign('fld_krit_id')
                ->references('fld_krit_id')
                ->on('kriteria')
                ->onDelete('cascade');
        });
    }
};
