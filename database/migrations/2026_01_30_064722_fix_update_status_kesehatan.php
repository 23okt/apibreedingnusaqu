<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kesehatan', function (Blueprint $table) {
            if (!Schema::hasColumn('kesehatan', 'status_kesehatan')) {
                $table->string('status_kesehatan')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('kesehatan', function (Blueprint $table) {
            if (Schema::hasColumn('kesehatan', 'status_kesehatan')) {
                $table->dropColumn('status_kesehatan');
            }
        });
    }
};