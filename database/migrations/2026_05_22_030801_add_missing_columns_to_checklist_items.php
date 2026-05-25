<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('checklist_items', function (Blueprint $table) {
            if (!Schema::hasColumn('checklist_items', 'item_code')) {
                $table->string('item_code')->unique()->after('id');
            }
            if (!Schema::hasColumn('checklist_items', 'item_name')) {
                $table->string('item_name')->after('item_code');
            }
            if (!Schema::hasColumn('checklist_items', 'category')) {
                $table->string('category')->after('item_name');
            }
            if (!Schema::hasColumn('checklist_items', 'description')) {
                $table->text('description')->nullable()->after('category');
            }
            if (!Schema::hasColumn('checklist_items', 'instruction')) {
                $table->text('instruction')->nullable()->after('description');
            }
            if (!Schema::hasColumn('checklist_items', 'status')) {
                $table->enum('status', ['active', 'inactive'])->default('active')->after('instruction');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('checklist_items', function (Blueprint $table) {
            $table->dropIndex(['item_code']);
            $table->dropColumn([
                'item_code',
                'item_name',
                'category',
                'description',
                'instruction',
                'status',
            ]);
        });
    }
};
