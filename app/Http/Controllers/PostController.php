<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostAudit;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;


class PostController extends Controller
{

    // 帖子列表
    public function listPage(Request $request)
    {
        return view('posts.list');
    }

    /**
     * 获取帖子列表
     *
     * @param Request $request 请求对象，包含分页和筛选参数
     * @return \Illuminate\Http\JsonResponse JSON格式的帖子列表数据
     */
    public function list(Request $request)
    {

        // 获取查询参数并设置默认值
        $page = (int) $request->query('page', 1);
        $pageSize = (int) $request->query('pageSize', 10);
        $status = $request->query('status');
        $keyword = $request->query('keyword');

        // 限制页面大小防止过度消耗资源
        $pageSize = min($pageSize, 100);

        // 创建查询构建器
        $query = Post::with(['user', 'images', 'videos']);

        // 应用过滤条件
        if ($status !== null && $status !== '') {
            $query->where('post_meta.audit_status', $status);
        }

        if ($keyword) {
            $query->where('post_meta.title', 'like', "%$keyword%");
        }

        // 使用simplePaginate避免count操作，提高性能
        $paginatedPosts = $query->orderBy('post_meta.created_at', 'desc')
            ->simplePaginate($pageSize, ['*'], 'page', $page);

        // 获取分页数据
        $posts = $paginatedPosts->items();

        // 构建响应数据
        $response = [
            'code' => 200,
            'msg' => 'success',
            'data' => $posts,
            'pagination' => [
                'current_page' => $page,
                'page_size' => $pageSize,
                'has_more' => $paginatedPosts->hasMorePages()
            ]
        ];

        return response()->json($response);
    }


    // 帖子详情页面
    public function itemPage(Request $request, $id)
    {
        return view('posts.item');
    }

    public function item(Request $request, $id)
    {
        // 加载用户关联
        $post = Post::with(['user', 'images', 'videos'])->find($id);

        if (!$post) {
            return response()->json([
                'code' => 404,
                'msg' => '帖子不存在',
                'data' => null
            ]);
        }

        return response()->json([
            'code' => 200,
            'msg' => 'success',
            'data' => $post
        ]);
    }

    // 帖子编辑
    public function edit(Request $request, $id)
    {
        return view('posts.edit');
    }

    // 审核
    public function audit(Request $request, $id)
    {

        // 审核帖子
        $post = Post::find($id);

        if (!$post) {
            return response()->json([
                'code' => 404,
                'msg' => '帖子不存在',
                'data' => null,
            ]);
        }

        // 审核状态
        $status = $request->input('status');
        // 拒绝原因
        $reason = $request->input('reason', '');
        // 拒绝备注
        $note = $request->input('note', '');


        if ($status === null || $status === '') {
            return response()->json([
                'code' => 400,
                'msg' => '请选择审核状态',
                'data' => null,
            ]);
        }

        // 更新审核状态
        $post->audit_status = $status;
        $post->save();

        // 添加审核记录
        $result = PostAudit::create([
            'post_id' => $id,
            'audit_status' => $status,
            'user_id' => 10000,
            'reason' => $reason,
            'note' => $note,
        ]);


        if (!$result) {
            return response()->json([
                'code' => 500,
                'msg' => '审核记录添加失败',
                'data' => null,
            ]);
        }

        return response()->json([
            'code' => 200,
            'msg' => '审核成功',
            'data' => $post,
        ]);
    }


    // 帖子删除
    public function destroy(Request $request, $id)
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json([
                'code' => 404,
                'msg' => '帖子不存在',
                'data' => null,
            ]);
        }
        // 状态设置为已删除
        $post->status = -1;
        // 操作人设置为系统
        $post->operator_id = 10000;
        $post->save();

        return response()->json([
            'code' => 200,
            'msg' => '帖子删除成功',
            'data' => null,
        ]);
    }

    // 恢复帖子
    public function restore(Request $request, $id)
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json([
                'code' => 404,
                'msg' => '帖子不存在',
                'data' => null,
            ]);
        }
        // 状态设置为正常
        $post->status = 1;
        // 操作人设置为系统
        $post->operator_id = 10000;
        $post->save();

        return response()->json([
            'code' => 200,
            'msg' => '帖子恢复成功',
            'data' => null,
        ]);
    }

    // 获取帖子审核历史
    public function getAuditHistory(Request $request, $id)
    {
        // 查询该帖子的所有审核记录
        $auditHistory = PostAudit::where('post_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        // 格式化历史记录为前端需要的格式
        $formattedHistory = [];
        foreach ($auditHistory as $record) {
            $statusText = match ($record->audit_status) {
                1 => '审核通过',
                -1 => '待复核',
                -2 => '已拒绝',
                default => '未知状态'
            };

            $formattedHistory[] = $record->created_at->format('Y-m-d H:i:s') . ' 管理员 操作：' . $statusText .
                ($record->reason ? '，原因：' . $record->reason : '') .
                ($record->note ? '，备注：' . $record->note : '');
        }

        return response()->json([
            'code' => 200,
            'msg' => 'success',
            'data' => $formattedHistory
        ]);
    }

    // 帖子评论列表
    public function itemComments(Request $request, $id)
    {
        return view('posts.item-comments');
    }
}
