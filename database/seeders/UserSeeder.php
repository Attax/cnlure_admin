<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * 生成指定范围内的用户ID（适用于auto_increment=100000的情况）
     */
    public function run(): void
    {
        // 设置要生成的用户ID范围
        $startId = 1;         // 起始ID
        $endId = 99999;       // 结束ID（小于100000）
        $totalUsers = $endId - $startId + 1;
        
        echo "开始生成 {$startId} 到 {$endId} 范围内的用户记录...\n";
        
        // 批次大小，控制内存使用
        $batchSize = 10000;
        // 已插入用户数
        $insertedCount = 0;
        
        // 批量插入用户数据
        while ($insertedCount < $totalUsers) {
            $users = [];
            // 当前批次的用户数量
            $currentBatchSize = min($batchSize, $totalUsers - $insertedCount);
            
            // 生成当前批次的用户数据
            for ($i = 0; $i < $currentBatchSize; $i++) {
                $userId = $startId + $insertedCount + $i;
                
                $users[] = [
                    'id' => $userId, // 明确指定ID值（小于100000）
                    'name' => 'User' . $userId,
                    'email' => 'user' . $userId . '@example.com',
                    'password' => bcrypt('password' . $userId), // 生成密码
                    'type' => rand(0, 1), // 随机用户类型
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            // 使用DB::table直接插入，明确指定ID字段
            // 注意：这种方式不会改变表的auto_increment值
            DB::table('user')->insert($users);
            
            $insertedCount += $currentBatchSize;
            echo "已插入 {$insertedCount}/{$totalUsers} 用户记录（ID范围：{$startId}-{$endId}）\n";
            
            // 可选：小延迟，避免系统负载过高
            // usleep(100000);
        }
        
        echo "用户记录生成完成！成功插入ID范围为 {$startId} 到 {$endId} 的 {$totalUsers} 条记录\n";
        echo "表的auto_increment设置保持不变，新插入的无ID记录将从100000开始自增\n";
    }
}