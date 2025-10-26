<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /**
     * 获取用户认证信息
     */
    public function verfiyInfo($userId)
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                return response()->json([
                    'code' => 1,
                    'message' => '用户不存在',
                    'data' => null
                ]);
            }

            // 这里假设用户表有verify_info字段存储认证信息
            // 实际应用中可能需要从单独的认证信息表中获取
            $verifyInfo = $user->verify_info ? json_decode($user->verify_info, true) : [];

            return response()->json([
                'code' => 0,
                'message' => 'success',
                'data' => [
                    'user_id' => $user->id,
                    'nickname' => $user->nickname,
                    'verify_status' => $user->auth_status ?? 0,
                    'verify_info' => $verifyInfo,
                    'submit_time' => $user->verify_submit_time ?? null
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 1,
                'message' => '获取认证信息失败: ' . $e->getMessage(),
                'data' => null
            ]);
        }
    }

    /**
     * 审核用户认证信息
     */
    public function auditVerifyInfo(Request $request, $userId)
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                return response()->json([
                    'code' => 1,
                    'message' => '用户不存在',
                    'data' => null
                ]);
            }

            $status = $request->input('status'); // 1: 通过, 2: 拒绝
            $remark = $request->input('remark', '');

            if (!in_array($status, [1, 2])) {
                return response()->json([
                    'code' => 1,
                    'message' => '无效的审核状态',
                    'data' => null
                ]);
            }

            // 更新用户认证状态
            $user->auth_status = $status;
            $user->verify_audit_time = now();
            $user->verify_remark = $remark;
            $user->save();

            return response()->json([
                'code' => 0,
                'message' => '审核成功',
                'data' => null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 1,
                'message' => '审核失败: ' . $e->getMessage(),
                'data' => null
            ]);
        }
    }

    /**
     * 更新用户认证信息
     */
    public function updateVerifyInfo(Request $request, $userId)
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                return response()->json([
                    'code' => 1,
                    'message' => '用户不存在',
                    'data' => null
                ]);
            }

            $verifyInfo = $request->input('verify_info', []);
            
            // 更新用户认证信息
            $user->verify_info = json_encode($verifyInfo, JSON_UNESCAPED_UNICODE);
            $user->auth_status = 0; // 重置为未认证状态，等待重新审核
            $user->save();

            return response()->json([
                'code' => 0,
                'message' => '更新成功',
                'data' => null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 1,
                'message' => '更新失败: ' . $e->getMessage(),
                'data' => null
            ]);
        }
    }

    /**
     * 禁用用户账号
     */
    public function ban($userId)
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                return response()->json([
                    'code' => 1,
                    'message' => '用户不存在',
                    'data' => null
                ]);
            }

            // 禁用用户（设置为-1表示封禁）
            $user->status = -1;
            $user->save();

            return response()->json([
                'code' => 0,
                'message' => '账号禁用成功',
                'data' => null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 1,
                'message' => '禁用失败: ' . $e->getMessage(),
                'data' => null
            ]);
        }
    }

    /**
     * 删除用户账号
     */
    public function destroy($userId)
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                return response()->json([
                    'code' => 1,
                    'message' => '用户不存在',
                    'data' => null
                ]);
            }

            // 删除用户（实际应用中可能需要软删除）
            $user->delete();

            return response()->json([
                'code' => 0,
                'message' => '账号删除成功',
                'data' => null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 1,
                'message' => '删除失败: ' . $e->getMessage(),
                'data' => null
            ]);
        }
    }
}
