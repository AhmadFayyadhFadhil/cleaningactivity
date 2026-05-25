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
        Schema::table('areas', function (Blueprint $table) {
            if (!Schema::hasColumn('areas', 'area_code')) {
                $table->string('area_code')->unique()->after('id');
            }
            if (!Schema::hasColumn('areas', 'area_name')) {
                $table->string('area_name')->after('area_code');
            }
            if (!Schema::hasColumn('areas', 'location')) {
                $table->string('location')->nullable()->after('area_name');
            }
            if (!Schema::hasColumn('areas', 'floor')) {
                $table->integer('floor')->nullable()->after('location');
            }
            if (!Schema::hasColumn('areas', 'building')) {
                $table->string('building')->nullable()->after('floor');
            }
            if (!Schema::hasColumn('areas', 'pic_user_id')) {
                $table->foreignId('pic_user_id')->nullable()->constrained('users')->onDelete('set null')->after('building');
            }
            if (!Schema::hasColumn('areas', 'status')) {
                $table->enum('status', ['active', 'inactive'])->default('active')->after('pic_user_id');
            }
            if (!Schema::hasColumn('areas', 'schedule_frequency')) {
                $table->string('schedule_frequency')->default('daily')->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('areas', function (Blueprint $table) {
            $table->dropIndex(['area_code']);
            $table->dropForeign(['pic_user_id']);
            $table->dropColumn([
                'area_code',
                'area_name',
                'location',
                'floor',
                'building',
                'pic_user_id',
                'status',
                'schedule_frequency',
            ]);
        });
    }
};
