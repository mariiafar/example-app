<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ChangeTimeFormatInApplicationsTable extends Migration
{
    public function up()
    {
        
        Schema::table('applications', function (Blueprint $table) {
            $table->time('time_new')->nullable()->after('time');
        });
        
       
        DB::statement('UPDATE applications SET time_new = TIME(time)');
        
       
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn('time');
        });
        
    
        Schema::table('applications', function (Blueprint $table) {
            $table->renameColumn('time_new', 'time');
        });
    }

    public function down()
    {
       
        Schema::table('applications', function (Blueprint $table) {
            $table->string('time_old')->nullable()->after('time');
        });
        
        DB::statement('UPDATE applications SET time_old = TIME_FORMAT(time, "%H:%i:%s")');
        
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn('time');
            $table->renameColumn('time_old', 'time');
        });
    }
}