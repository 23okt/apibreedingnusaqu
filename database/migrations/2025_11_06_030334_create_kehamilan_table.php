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
        Schema::create('kehamilan', function (Blueprint $table) {
            $table->id('id_kehamilan')->primary();
            $table->string('kode_kehamilan');
            $table->unsignedBigInteger('breeding_id')->nullable();
            $table->date('check_date');
            $table->enum('status', ['tahap kesatu','tahap kedua','tahap ketiga','gagal']);
            $table->string('notes', 255);
            $table->string('photos', 255);
            $table->timestamps();

            $table->foreign('breeding_id')->references('id_breeding')->on('perkawinan')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kehamilan');
    }
};