<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->unsignedBigInteger('master_id')->nullable()->after('id');
            $table->foreign('master_id')->references('id')->on('users')->onDelete('cascade');
            $table->dropColumn('master_name');
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            // Сначала удаляем внешний ключ, потом колонку
            $table->dropForeign(['master_id']);
            $table->dropColumn('master_id');

            // Возвращаем поле master_name обратно
            $table->string('master_name')->nullable()->after('id');
        });
    }
};