<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cleaning_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->constrained('areas')->onDelete('cascade');
            $table->dateTime('schedule_date');
            $table->time('schedule_time');
            $table->foreignId('assigned_to_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['scheduled', 'in-progress', 'completed', 'cancelled'])->default('scheduled');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['area_id', 'schedule_date']);
            $table->index(['assigned_to_id', 'status']);
            $table->index(['supervisor_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cleaning_schedules');
    }
};