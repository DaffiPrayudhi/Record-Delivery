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
        Schema::table('master_qty', function (Blueprint $table) {
            $table->tinyInteger('flag')->nullable(); // Menambahkan kolom description
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_qty', function (Blueprint $table) {
            $table->dropColumn('flag'); // Menghapus kolom description
        });
    }
};
