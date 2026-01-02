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
        Schema::create('investasi', function (Blueprint $table) {
            $table->id('id_investasi')->primary();
            $table->string('kode_investasi');
            $table->unsignedBigInteger('users_id')->nullable();
            $table->integer('jumlah_inves');
            $table->string('jumlah_inves_terbilang');
            $table->string('metode_pembayaran');
            $table->string('bukti_pembayaran');
            $table->string('description');
            $table->enum('status', ['Diterima','Gagal']);
            $table->date('tanggal_investasi');
            $table->timestamps();
            
            $table->foreign('users_id')->references('id_users')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment');
    }
};