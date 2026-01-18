<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('applications', function (Blueprint $table) {
            // Проверяем существование каждого столбца перед добавлением
            if (!Schema::hasColumn('applications', 'deposit')) {
                $table->decimal('deposit', 10, 2)->default(0);
            }
            
            if (!Schema::hasColumn('applications', 'payment_status')) {
                $table->string('payment_status')->default('unpaid');
            }
            
            if (!Schema::hasColumn('applications', 'payment_id')) {
                $table->string('payment_id')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['deposit', 'payment_status', 'payment_id']);
        });
    }
};