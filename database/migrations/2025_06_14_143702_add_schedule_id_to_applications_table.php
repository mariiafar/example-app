<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('applications', function (Blueprint $table) {
        $table->foreignId('schedule_id')->nullable()->constrained()->nullOnDelete();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down()
{
    Schema::table('applications', function (Blueprint $table) {
        $table->dropForeign(['schedule_id']);
        $table->dropColumn('schedule_id');
    });
}
};
