<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('applications', function (Blueprint $table) {

        if (!Schema::hasColumn('applications', 'payment_status')) {
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
        }

        if (!Schema::hasColumn('applications', 'payment_method')) {
            $table->string('payment_method')->nullable();
        }

        if (!Schema::hasColumn('applications', 'payment_id')) {
            $table->string('payment_id')->nullable();
        }
    });
}

public function down()
{
    Schema::table('applications', function (Blueprint $table) {
        $table->dropColumn(['payment_status', 'payment_method', 'payment_id']);
    });

   
}
};

