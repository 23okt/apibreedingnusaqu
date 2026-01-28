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
        Schema::table('perkawinan', function (Blueprint $table) {
            $table->renameColumn('tanggal_perkiraan_lahir','tanggal_pkb');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perkawinan', function (Blueprint $table) {
            $table->renameColumn('tanggal_pkb','tanggal_perkiraan_lahir');
        });
    }
};