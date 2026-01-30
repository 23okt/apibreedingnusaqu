<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kelahiran', function (Blueprint $table) {
            if (Schema::hasColumn('kelahiran', 'photos')) {
                $table->dropColumn('photos');
            }
        });
    }

    public function down(): void
    {
        Schema::table('kelahiran', function (Blueprint $table) {
            if (!Schema::hasColumn('kelahiran', 'photos')) {
                $table->string('photos')->nullable();
            }
        });
    }
};