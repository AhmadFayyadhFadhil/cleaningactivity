<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cleaning_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('cleaning_schedules')->onDelete('cascade');
            $table->foreignId('verified_by_id')->constrained('users')->onDelete('restrict');
            $table->enum('verification_status', ['approved', 'rejected', 'need-revision'])->default('need-revision');
            $table->text('notes')->nullable();
            $table->text('findings')->nullable();
            $table->dateTime('verified_at')->nullable();
            $table->timestamps();
            
            $table->index(['schedule_id', 'verification_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cleaning_verifications');
    }
};