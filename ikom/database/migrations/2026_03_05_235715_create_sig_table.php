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
        Schema::create('sig', function (Blueprint $table) {
            $table->string('fld_sig_id')->primary();
            $table->enum('fld_sig_nama', ['SIG 1', 'SIG 2', 'SIG 3']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sig');
    }
};
