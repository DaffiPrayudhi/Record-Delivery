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
        Schema::create('record_simpanan', function (Blueprint $table) {
            $table->id();
            $table->string('no_transaksi',25)->nullable;
            $table->date('tgl_bln_thn')->nullable;
            $table->string('model',15)->nullable;
            $table->char('plant_dest', 5)->nullable;
            $table->string('lot_number', 35)->nullable;
            $table->string('pic', 50)->nullable;
            $table->integer('qty')->nullable;
            $table->tinyInteger('flag')->nullable;
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('record_simpanan');
    }
};
