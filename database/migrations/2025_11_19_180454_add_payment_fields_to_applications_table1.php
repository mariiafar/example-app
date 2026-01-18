<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('applications', function (Blueprint $table) {
        if (!Schema::hasColumn('applications', 'deposit')) {
            $table->decimal('deposit', 10, 2)->default(0);
        }
        if (!Schema::hasColumn('applications', 'payment_status')) {
            $table->string('payment_status')->default('unpaid');
        }
        if (!Schema::hasColumn('applications', 'payment_method')) {
            $table->string('payment_method')->nullable();
        }
        if (!Schema::hasColumn('applications', 'payment_id')) {
            $table->string('payment_id')->nullable();
        }
        if (!Schema::hasColumn('applications', 'time_end')) {
            $table->time('time_end')->nullable();
        }
    });
}
};
