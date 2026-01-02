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
        Schema::create('obat', function (Blueprint $table) {
            $table->id('id_obat')->primary();
            $table->string('kode_obat');
            $table->string('nama_obat');
            $table->unsignedInteger('stock_obat');
            $table->string('type_obat');
            $table->unsignedInteger('isi_obat');
            $table->unsignedInteger('total_obat')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('obat');
    }
};