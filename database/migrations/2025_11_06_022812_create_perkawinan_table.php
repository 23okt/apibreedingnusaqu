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
        Schema::create('perkawinan', function (Blueprint $table) {
            $table->id('id_breeding')->primary();
            $table->string('kode_breeding');
            $table->unsignedBigInteger('female_id')->nullable();
            $table->unsignedBigInteger('male_id')->nullable();
            $table->date('tanggal_perkiraan_lahir');
            $table->enum('status', ['pregnant', 'failed', 'completed']);
            $table->string('photo1');
            $table->string('photo2');
            $table->string('photo3');
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->foreign('female_id')->references('id_product')->on('product')->onDelete('set null');
            $table->foreign('male_id')->references('id_product')->on('product')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perkawinan');
    }
};