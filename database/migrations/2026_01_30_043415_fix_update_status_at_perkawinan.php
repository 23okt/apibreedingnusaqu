<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('perkawinan', function (Blueprint $table) {
            if (Schema::hasColumn('perkawinan', 'photo1')) {
                $table->dropColumn('photo1');
            }

            if (Schema::hasColumn('perkawinan', 'photo2')) {
                $table->dropColumn('photo2');
            }

            if (Schema::hasColumn('perkawinan', 'photo3')) {
                $table->dropColumn('photo3');
            }
        });

        DB::statement("
            ALTER TABLE perkawinan 
            MODIFY status VARCHAR(50)
        ");
    }

    public function down(): void
    {
        Schema::table('perkawinan', function (Blueprint $table) {
            if (!Schema::hasColumn('perkawinan', 'photo1')) {
                $table->string('photo1')->nullable();
            }

            if (!Schema::hasColumn('perkawinan', 'photo2')) {
                $table->string('photo2')->nullable();
            }

            if (!Schema::hasColumn('perkawinan', 'photo3')) {
                $table->string('photo3')->nullable();
            }
        });

        DB::statement("
            ALTER TABLE perkawinan
            MODIFY status ENUM('failed','completed','pregnant')
        ");
    }
};