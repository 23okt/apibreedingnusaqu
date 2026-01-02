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
        Schema::create('users', function (Blueprint $table) {
            $table->id('id_users')->primary(); //id dengan increment
            $table->string('kode_unik');    //kustomisasi untuk kode customer/admin
            $table->string('nama_users', 255);
            $table->string('pass_users', 255);
            $table->string('alamat', 255)->nullable();
            $table->string('no_telp', 255)->nullable();
            $table->enum('role', ['admin','customer'])->default('customer');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};