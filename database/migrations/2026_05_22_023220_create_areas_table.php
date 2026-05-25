<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('area_code')->unique();
            $table->string('area_name');
            $table->string('location')->nullable();
            $table->integer('floor')->nullable();
            $table->string('building')->nullable();
            $table->foreignId('pic_user_id')->nullable()->constrained('users')->onDelete('set null');
$table->string('status')->default('active');
            $table->index('status');
            $table->string('schedule_frequency')->default('daily'); // daily, weekly, monthly
$table->timestamps();
            
            $table->index('area_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('areas');
    }
};