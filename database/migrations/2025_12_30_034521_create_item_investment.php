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
        Schema::create('item_investment', function (Blueprint $table) {
            $table->id('id_item_invest');
            $table->unsignedBigInteger('investasi_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->bigInteger('jumlah_investasi');
            $table->timestamps();

            $table->foreign('investasi_id')->references('id_investasi')->on('investasi')->onDelete('set null');
            $table->foreign('product_id')->references('id_product')->on('product')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_investment');
    }
};