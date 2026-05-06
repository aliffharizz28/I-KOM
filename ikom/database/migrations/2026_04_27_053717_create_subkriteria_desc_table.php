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
        Schema::create('subkriteria_desc', function (Blueprint $table) {
            $table->id('fld_desc_id');
            $table->unsignedBigInteger('fld_sub_id');
            $table->string('fld_desc_text');
            $table->integer('fld_desc_markah')->default(5);
            $table->timestamps();

            $table->foreign('fld_sub_id')
                  ->references('fld_sub_id')
                  ->on('subkriteria')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subkriteria_desc');
    }
};
