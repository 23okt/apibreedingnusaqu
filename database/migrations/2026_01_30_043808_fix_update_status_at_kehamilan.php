<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kehamilan', function (Blueprint $table) {
            if (Schema::hasColumn('kehamilan', 'photos')) {
                $table->dropColumn('photos');
            }
        });

        DB::statement("
            ALTER TABLE kehamilan
            MODIFY status VARCHAR(50)
        ");
    }

    public function down(): void
    {
        Schema::table('kehamilan', function (Blueprint $table) {
            if (!Schema::hasColumn('kehamilan', 'photos')) {
                $table->string('photos')->nullable();
            }
        });

        DB::statement("
            ALTER TABLE kehamilan
            MODIFY status ENUM(
                'gagal',
                'tahap kesatu',
                'tahap kedua',
                'tahap ketiga'
            )
        ");
    }
};