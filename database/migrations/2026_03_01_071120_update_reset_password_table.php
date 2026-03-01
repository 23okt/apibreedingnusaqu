<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reset_password', function (Blueprint $table) {
            $table->string('no_telp', 25)->change();
        });
    }

    public function down(): void
    {
        Schema::table('reset_password', function (Blueprint $table) {
            $table->integer('no_telp')->change();
        });
    }
};