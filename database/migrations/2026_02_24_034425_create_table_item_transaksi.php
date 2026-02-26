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
        Schema::create('item_transaksi', function (Blueprint $table) {
            $table->id('id_item_transaksi');
            $table->unsignedBigInteger('transaksi_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->integer('harga_beli');
            $table->integer('harga_jual');
            $table->integer('bobot');
            $table->timestamps();

            $table->foreign('transaksi_id')->references('id_transaksi')->on('transaksi')->onDelete('set null');
            $table->foreign('product_id')->references('id_product')->on('product')->onDeleted('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_transaksi');
    }
};