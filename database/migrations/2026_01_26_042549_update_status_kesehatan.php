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
        Schema::table('kesehatan', function (Blueprint $table) {
            $table->string('status_kesehatan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kesehatan', function (Blueprint $table) {
            $table->dropColumn('status_kesehatan')->nullable();
        });
    }
};