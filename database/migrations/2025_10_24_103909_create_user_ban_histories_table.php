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
        Schema::create('user_ban_histories', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->index('idx_user_id'); // 被封禁用户ID
            $table->integer('operator_id')->index('idx_operator_id'); // 操作人ID（管理员）
            $table->string('reason', 200); // 封禁原因
            $table->integer('status_before'); // 封禁前状态
            $table->integer('status_after'); // 封禁后状态
            $table->timestamp('ban_time'); // 封禁时间
            $table->timestamp('unban_time')->nullable(); // 解封时间
            $table->tinyInteger('is_active')->default(1); // 是否当前有效封禁
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_ban_histories');
    }
};
