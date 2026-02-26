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
        $table->dropForeign(['product_id']);
        $table->dropColumn(['product_id', 'harga_beli', 'harga_jual', 'bobot',]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table){
            $table->unsignedBigInteger('product_id')->after('kode_transaksi');
            $table->integer('harga_beli')->after('nama_pembeli');
            $table->integer('harga_jual')->after('harga_beli');
            $table->integer('bobot')->after('harga_jual');
            $table->foreign('product_id')->references('id_product')->on('product')->onDelete('cascade');
        });
    }
};