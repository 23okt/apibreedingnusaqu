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
        Schema::table('transaksi', function (Blueprint $table){
            $table->integer('jumlah_nominal')->after('nama_pembeli');
            $table->string('jumlah_nominal_terbilang')->after('jumlah_nominal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table){
            $table->dropColumn('jumlah_nominal');
            $table->dropColumn('jumlah_nominal_terbilang');
        });
    }
};