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
        Schema::table('record', function (Blueprint $table) {
            $table->date('tgl_bln_thn_dlv')->nullable()->after('tgl_bln_thn'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('record', function (Blueprint $table) {
            $table->dropColumn('tgl_bln_thn_dlv'); 
        });
    }
};
