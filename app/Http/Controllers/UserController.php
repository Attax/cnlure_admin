<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

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

        // 构建查询
        $query = User::query();

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

        // 禁用用户
        $user->status = 0;
        $user->save();

        // 记录操作日志
        // TODO: 记录禁用用户的操作日志，包括禁用原因

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

        // 启用用户
        $user->status = 1;
        $user->save();

        // 记录操作日志
        // TODO: 记录启用用户的操作日志

        return response()->json([
            'code' => 0,
            'message' => '用户已启用',
            'data' => $user
        ]);
    }


}
