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
        Schema::create('kelahiran', function (Blueprint $table) {
            $table->id('id_kelahiran')->primary();
            $table->string('kode_kelahiran');
            $table->unsignedBigInteger('breeding_id')->nullable();
            $table->date('birth_date');
            $table->integer('offspring_count');
            $table->string('photos', 255);
            $table->string('notes');
            $table->timestamps();

            $table->foreign('breeding_id')->references('id_breeding')->on('perkawinan')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelahiran');
    }
};