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
        Schema::create('kesehatan', function (Blueprint $table) {
            $table->id('id_kesehatan')->primary();
            $table->string('kode_kesehatan');
            $table->string('diagnosa');
            $table->date('check_date');
            $table->string('photo1');
            $table->string('photo2');
            $table->string('photo3');
            $table->string('notes');
            $table->enum('status_kesehatan', ['sehat','sakit','penyembuhan']);
            $table->unsignedBigInteger('product_id')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id_product')->on('product')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health');
    }
};