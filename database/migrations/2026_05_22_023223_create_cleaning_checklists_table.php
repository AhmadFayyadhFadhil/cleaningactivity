<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cleaning_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('cleaning_schedules')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('checklist_items')->onDelete('restrict');
            $table->enum('condition', ['Clean', 'Dirty', 'Damaged'])->default('Clean');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['schedule_id', 'item_id']);
            $table->index(['schedule_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cleaning_checklists');
    }
};