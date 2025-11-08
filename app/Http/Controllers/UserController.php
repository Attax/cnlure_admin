<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class UserController extends Controller
{
    /**
     * 显示用户管理页面
     */
    public function list(Request $request)
    {
        return view('users.list');
    }

    /**
     * 获取用户列表数据
     */
    public function getList(Request $request)
    {
        // 分页参数
        $page = (int) $request->query('page', 1);
        $pageSize = (int) $request->query('page_size', 10);
        $keyword = $request->query('keyword', '');
        $status = $request->query('status', '');

        // 构建查询，只显示ID大于等于100001的用户（排除内部测试账号）
        $query = User::query()->where('id', '>=', 100001);

        // 关键词搜索（用户ID、昵称、手机号）
        if (!empty($keyword)) {
            $query->where(function (Builder $query) use ($keyword) {
                $query->where('id', $keyword)
                    ->orWhere('nickname', 'like', '%' . $keyword . '%')
                    ->orWhere('phone', 'like', '%' . $keyword . '%');
            });
        }

        // 状态筛选
        if ($status !== '') {
            $query->where('status', $status);
        }


        // dd($query->toSql());
        // 分页查询


        $paginatedUsers = $query->orderBy('id', 'desc')
            ->simplePaginate($pageSize, ['*'], 'page', $page);

        $users = $paginatedUsers->items();

        return response()->json([
            'code' => 200,
            'msg' => 'success',
            'data' => $users,
            'pagination' => [
                'current_page' => $page,
                'page_size' => $pageSize,
                'has_more' => $paginatedUsers->hasMorePages()
            ]
        ]);
    }

    /**
     * 获取用户详情
     */
    public function detail(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'code' => 1,
                'message' => '用户不存在'
            ]);
        }

        return response()->json([
            'code' => 0,
            'message' => 'success',
            'data' => $user
        ]);
    }

    /**
     * 编辑用户信息
     */
    public function edit(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'code' => 1,
                'message' => '用户不存在'
            ]);
        }

        // 验证请求数据
        $request->validate([
            'nickname' => 'required|max:50',
            'phone' => 'nullable|regex:/^1[3-9]\d{9}$/',
            'email' => 'nullable|email',
            'status' => 'required|in:0,1,2',
            'bio' => 'nullable|max:200'
        ]);

        // 更新用户信息
        $user->nickname = $request->input('nickname');
        $user->phone = $request->input('phone');
        $user->email = $request->input('email');
        $user->status = $request->input('status');
        $user->bio = $request->input('bio');
        $user->save();

        return response()->json([
            'code' => 0,
            'message' => '更新成功',
            'data' => $user
        ]);
    }

    /**
     * 禁用用户
     */
    public function ban(Request $request, $id)
    {

        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'code' => 1,
                'message' => '用户不存在'
            ]);
        }

        // 验证请求数据
        $request->validate([
            'reason' => 'required|max:200'
        ]);

        // 获取当前登录管理员ID（这里假设从session或auth中获取，实际需要根据项目认证机制调整）
        $operatorId = 1; // 临时硬编码，实际应该从auth中获取

        // 禁用用户（设置为-1表示封禁）
        $statusBefore = $user->status;
        $user->status = -1;
        $user->save();

        // 记录状态变更历史
        UserStatusHistory::create([
            'user_id' => $id,
            'operator_id' => $operatorId,
            'reason' => $request->input('reason'),
            'status_before' => $statusBefore,
            'status_after' => -1,
            'ban_time' => now(),
            'is_active' => 1
        ]);

        // 使用Laravel的HTTP客户端调用踢下线接口
        $this->kickUserOut($id);

        return response()->json([
            'code' => 0,
            'message' => '用户已禁用',
            'data' => $user
        ]);
    }

    /**
     * 启用用户
     */
    public function unban(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'code' => 1,
                'message' => '用户不存在'
            ]);
        }

        // 获取当前登录管理员ID（这里假设从session或auth中获取，实际需要根据项目认证机制调整）
        $operatorId = 1; // 临时硬编码，实际应该从auth中获取

        // 找到该用户的当前有效状态变更记录
        $activeStatusHistory = UserStatusHistory::where('user_id', $user->id)
            ->where('is_active', 1)
            ->first();

        // 如果找到有效状态变更记录，更新为已结束
        if ($activeStatusHistory) {
            $activeStatusHistory->unban_time = now();
            $activeStatusHistory->is_active = 0;
            $activeStatusHistory->save();
        }

        // 启用用户（设置为0表示默认活动状态）
        $statusBefore = $user->status;
        $user->status = 0;
        $user->save();

        // 记录状态变更历史（记录解封操作）
        UserStatusHistory::create([
            'user_id' => $id,
            'operator_id' => $operatorId,
            'reason' => '用户已解封',
            'status_before' => $statusBefore,
            'status_after' => 0,
            'ban_time' => now(),
            'unban_time' => now(),
            'is_active' => 0
        ]);

        // 使用Laravel的HTTP客户端调用踢下线接口
        $this->kickUserOut($id);

        return response()->json([
            'code' => 0,
            'message' => '用户已启用',
            'data' => $user
        ]);
    }

    /**
     * 获取用户状态变更历史
     */
    public function getStatusHistory(Request $request, $userId)
    {
        // 验证用户是否存在
        $user = User::find($userId);
        if (!$user) {
            return response()->json([
                'code' => 404,
                'message' => '用户不存在',
                'data' => null
            ]);
        }

        // 获取分页参数
        $page = (int) $request->query('page', 1);
        $pageSize = (int) $request->query('page_size', 10);

        // 查询用户的状态变更历史
        $statusHistories = UserStatusHistory::where('user_id', $userId)
            ->with('operator') // 加载操作人信息
            ->orderBy('created_at', 'desc')
            ->simplePaginate($pageSize, ['*'], 'page', $page);

        return response()->json([
            'code' => 0,
            'message' => '获取成功',
            'data' => $statusHistories->items(),
            'pagination' => [
                'current_page' => $statusHistories->currentPage(),
                'page_size' => $pageSize,
                'has_more' => $statusHistories->hasMorePages()
            ]
        ]);
    }

    /**
     * 踢下线用户
     */
    public function kickUserOut(int $userId)
    {
        // 根据环境变量选择不同的接口地址
        $environment = env('APP_ENV', 'local');

        // 如果是local_production 或者production环境，不调用接口
        if ($environment === 'local_production' || $environment === 'production') {
            // 对于local_production或production环境使用正式域名
            $baseUrl = 'https://api.cnlure.com';
        } else {
            // 对于local环境使用本地接口
            $baseUrl = 'http://localhost:8789';
        }

        $endpoint = '/system-hooks/users/kickout';
        $url = $baseUrl . $endpoint;

        // 使用Laravel的HTTP客户端调用踢下线接口
        try {

            // 生成sign
            $timestamp = time();
            $nonce = uniqid();
            $token = env('SYSTEM_INTERNAL_TOKEN');
            if (!$token) {
                throw new \Exception('SYSTEM_INTERNAL_TOKEN 未配置');
            }

            $data = [
                $timestamp,
                $nonce,
                $token
            ];

            // 将token、timestamp、nonce三个参数进行字典序排序
            ksort($data);
            // 拼接字符串
            $rawText = join('', $data);

            $signature = hash('sha1', $rawText);

            return Http::timeout(5)
                ->withHeaders([
                    'X-Signature' => $signature,
                    'X-Timestamp' => $timestamp,
                    'X-Nonce' => $nonce,
                ])->post($url, [
                    'user_id' => $userId
                ]);
        } catch (\Exception $e) {
            // 记录错误但不影响踢下线操作
            \Log::error('踢下线请求失败: ' . $e->getMessage() . ', URL: ' . $url);
            return false;
        }
    }
}
