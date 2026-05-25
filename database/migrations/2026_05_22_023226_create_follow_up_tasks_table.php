<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('follow_up_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('verification_id')->constrained('cleaning_verifications')->onDelete('cascade');
            $table->text('issue_description');
            $table->enum('priority', ['Low', 'Medium', 'High', 'Critical'])->default('Medium');
            $table->foreignId('assigned_to_id')->constrained('users')->onDelete('restrict');
            $table->enum('status', ['Open', 'In Progress', 'Closed'])->default('Open');
            $table->dateTime('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();
            
            $table->index(['status']);
            $table->index(['assigned_to_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follow_up_tasks');
    }
};