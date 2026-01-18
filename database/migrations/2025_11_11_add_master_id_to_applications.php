<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->unsignedBigInteger('master_id')->nullable()->after('service_id');
            $table->foreign('master_id')->references('id')->on('users')->onDelete('cascade');
            $table->dropColumn('master');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            
            $table->dropForeign(['master_id']);
            $table->dropColumn('master_id');

        
            $table->string('master')->nullable()->after('service_id');
        });
    }
};
