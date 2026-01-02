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
        Schema::create('item_penanganan', function (Blueprint $table) {
            $table->id('id_item_penanganan');
            $table->unsignedBigInteger('penanganan_id')->nullable();
            $table->unsignedBigInteger('obat_id')->nullable();
            $table->integer('jumlah_terpakai');
            $table->timestamps();

            $table->foreign('penanganan_id')->references('id_penanganan')->on('penanganan')->onDelete('set null');
            $table->foreign('obat_id')->references('id_obat')->on('obat')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_penanganan');
    }
};