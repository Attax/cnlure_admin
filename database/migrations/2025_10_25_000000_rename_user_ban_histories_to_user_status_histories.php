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
        // 重命名表
        Schema::rename('user_ban_histories', 'user_status_histories');
        
        // 更新索引名称以匹配新表名（可选，但推荐）
        DB::statement('ALTER TABLE user_status_histories RENAME INDEX idx_user_id TO idx_user_status_user_id');
        DB::statement('ALTER TABLE user_status_histories RENAME INDEX idx_operator_id TO idx_user_status_operator_id');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 恢复原表名
        Schema::rename('user_status_histories', 'user_ban_histories');
        
        // 恢复原索引名称
        DB::statement('ALTER TABLE user_ban_histories RENAME INDEX idx_user_status_user_id TO idx_user_id');
        DB::statement('ALTER TABLE user_ban_histories RENAME INDEX idx_user_status_operator_id TO idx_operator_id');
    }
};