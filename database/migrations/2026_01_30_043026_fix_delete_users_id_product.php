<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product', function (Blueprint $table) {
            if (Schema::hasColumn('product', 'users_id')) {
                $table->dropForeign(['users_id']);
                $table->dropColumn('users_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('product', function (Blueprint $table) {
            if (!Schema::hasColumn('product', 'users_id')) {
                $table->unsignedBigInteger('users_id')->nullable();

                $table->foreign('users_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
            }
        });
    }
};