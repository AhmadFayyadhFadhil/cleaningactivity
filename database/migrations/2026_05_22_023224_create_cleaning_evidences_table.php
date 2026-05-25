<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cleaning_evidences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('cleaning_schedules')->onDelete('cascade');
            $table->foreignId('checklist_id')->nullable()->constrained('cleaning_checklists')->onDelete('set null');
            $table->enum('photo_type', ['Before', 'After', 'Issue'])->default('After');
            $table->string('file_path');
            $table->string('file_name');
            $table->integer('file_size')->nullable();
            $table->foreignId('uploaded_by_id')->constrained('users')->onDelete('restrict');
            $table->timestamps();
            
            $table->index(['schedule_id', 'photo_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cleaning_evidences');
    }
};