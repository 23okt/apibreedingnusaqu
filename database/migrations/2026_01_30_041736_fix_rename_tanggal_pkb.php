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
            if (Schema::hasColumn('perkawinan', 'tanggal_perkiraan_lahir')) {
                $table->renameColumn('tanggal_perkiraan_lahir','tanggal_pkb');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};