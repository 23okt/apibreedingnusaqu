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
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id('id_transaksi')->primary();
            $table->string('kode_transaksi');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('users_id')->nullable();
            $table->decimal('harga_beli', 12,2);
            $table->decimal('harga_jual', 12,2);
            $table->integer('bobot');
            $table->timestamps();

            $table->foreign('product_id')->references('id_product')->on('product')->onDelete('set null');
            $table->foreign('users_id')->references('id_users')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};