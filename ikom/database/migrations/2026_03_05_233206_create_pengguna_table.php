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
        Schema::create('pengguna', function (Blueprint $table) {
            $table->id('fld_user_id');// akan jadi 1,2,3,4,5
            $table->string('fld_user_nama');
            $table->string('fld_user_email')->unique();
            $table->string('fld_user_pass');
            $table->integer('fld_user_role'); // 1 untuk pk, 2 untuk psig, 3 untuk pelajar
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengguna');
    }
};
