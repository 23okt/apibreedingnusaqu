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
        Schema::create('item_kelahiran', function (Blueprint $table) {
            $table->id('id_item_kelahiran');
            $table->unsignedBigInteger('kelahiran_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->timestamps();

            $table->foreign('kelahiran_id')->references('id_kelahiran')->on('kelahiran')->onDelete('set null');
            $table->foreign('product_id')->references('id_product')->on('product')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_kelahiran');
    }
};