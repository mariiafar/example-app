<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('client_name');
            $table->string('phone', 50);
            $table->string('email')->nullable();
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->string('source', 50);
            $table->string('status', 50);
            $table->string('master');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};