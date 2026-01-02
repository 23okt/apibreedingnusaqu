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
        Schema::create('penanganan', function (Blueprint $table) {
            $table->id('id_penanganan');
            $table->unsignedBigInteger('kesehatan_id')->nullable();
            $table->string('kode_penanganan');
            $table->string('judul_penanganan');
            $table->string('catatan_penanganan');
            $table->string('tanggal_penanganan');
            $table->timestamps();

            $table->foreign('kesehatan_id')->references('id_kesehatan')->on('kesehatan')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penanganan');
    }
};