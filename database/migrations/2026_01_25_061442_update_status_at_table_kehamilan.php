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
        Schema::table('kehamilan', function (Blueprint $table) {
            $table->dropColumn('photos');
        });
        DB::statement("
            ALTER TABLE kehamilan
            MODIFY status VARCHAR(50)
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kehamilan', function (Blueprint $table) {
            $table->string('photos')->nullable();
        });

        DB::statement("
            ALTER TABLE kehamilan
            MODIFY status ENUM('gagal','tahap kesatu','tahap kedua', 'tahap ketiga')
        ");
    }
};