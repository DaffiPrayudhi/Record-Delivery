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
        Schema::create('record_receh', function (Blueprint $table) {
            $table->id();
            $table->string('no_transaksi',25);
            $table->date('tgl_bln_thn');
            $table->string('model',15);
            $table->char('plant_dest', 5);
            $table->string('lot_number', 35);
            $table->string('pic', 50);
            $table->integer('qty');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('record_receh');
    }
};
