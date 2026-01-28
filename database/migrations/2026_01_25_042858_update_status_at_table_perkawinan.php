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
            $table->dropColumn('photo1');
            $table->dropColumn('photo2');
            $table->dropColumn('photo3');
        });
       DB::statement("
            ALTER TABLE perkawinan 
            MODIFY status VARCHAR(50)
        ");
    }

    public function down(): void
    {
        Schema::table('perkawinan', function (Blueprint $table) {
            $table->string('photo1')->nullable();
            $table->string('photo2')->nullable();
            $table->string('photo3')->nullable();
        });

        DB::statement("
            ALTER TABLE perkawinan
            MODIFY status ENUM('failed','completed','pregnant')
        ");
    }
};