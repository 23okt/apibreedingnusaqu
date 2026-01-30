<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('item_kelahiran')) {
            Schema::create('item_kelahiran', function (Blueprint $table) {
                $table->id('id_item_kelahiran');

                $table->unsignedBigInteger('kelahiran_id')->nullable();
                $table->unsignedBigInteger('product_id')->nullable();

                $table->timestamps();
            });

            // foreign key dipisah supaya tidak menggagalkan create table
            Schema::table('item_kelahiran', function (Blueprint $table) {
                if (Schema::hasTable('kelahiran')) {
                    $table->foreign('kelahiran_id')
                        ->references('id_kelahiran')
                        ->on('kelahiran')
                        ->onDelete('set null');
                }

                if (Schema::hasTable('product')) {
                    $table->foreign('product_id')
                        ->references('id_product')
                        ->on('product')
                        ->onDelete('set null');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('item_kelahiran');
    }
};