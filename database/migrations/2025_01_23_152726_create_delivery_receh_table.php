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
        Schema::create('delivery_receh', function (Blueprint $table) {
            $table->id();
            $table->string('no_transaksi',25)->nullable;
            $table->datetime('tgl_bln_thn')->nullable;
            $table->string('part_number',50)->nullable;
            $table->string('serial_number',50)->nullable;
            $table->string('lot_number', 30)->nullable;
            $table->integer('qty')->nullable;
            $table->tinyInteger('flag')->nullable;
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_receh');
    }
};
