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
        Schema::create('product', function (Blueprint $table) {
            $table->id('id_product')->primary();
            $table->string('kode_product');
            $table->string('nama_product');
            $table->string('jenis_product');
            $table->string('type_product');
            $table->enum('gender', ['male', 'female']);
            $table->date('birth_date')->nullable();
            $table->integer('bobot');
            $table->decimal('harga_beli', 12,2);
            $table->decimal('harga_jual', 12,2);
            $table->string('photo1', 255)->nullable();
            $table->string('photo2', 255)->nullable();
            $table->string('photo3', 255)->nullable();
            $table->enum('status', ['Terjual','Hidup', 'Mati']);
            $table->unsignedBigInteger('mother_id')->nullable();
            $table->unsignedBigInteger('father_id')->nullable();
            $table->unsignedBigInteger('users_id')->nullable();
            $table->unsignedBigInteger('kandang_id')->nullable();
            $table->timestamps();

            $table->foreign('mother_id')->references('id_product')->on('product')->onDelete('set null');
            $table->foreign('father_id')->references('id_product')->on('product')->onDelete('set null');
            $table->foreign('kandang_id')->references('id_kandang')->on('kandang')->onDelete('set null');
            $table->foreign('users_id')->references('id_users')->on('users')->onDelete('set null');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goats');
    }
};