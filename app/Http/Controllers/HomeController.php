<?php

namespace App\Http\Controllers;

use App\Models\Comment;
//use App\Models\FishingSpot;
use App\Models\Post;
//use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    //

    /**
     * 概况
     *
     * @return \Illuminate\Http\JsonResponse JSON格式的概况数据
     */
    public function overview()
    {

        // 获取待审核帖子数量
        $postsTaskCount = Post::whereIn('audit_status', [0, -1])->count();
        // 获取待审核评论数量
        $commentsTaskCount = Comment::whereIn('audit_status', [0, -1])->count();
        // 获取正式用户数量（ID>=100001）
        $userCount = User::where('id', '>=', 100001)->count();

        // 获取钓场数量
        //$fishingSpotCount = FishingSpot::count();


        // 获取新增正式用户数量（ID>=100001）
        $newUsersCount = User::where('id', '>=', 100001)->where('created_at', '>=', now()->subMonth())->count();

        // 获取新增帖子数量
        $newPostsCount = Post::where('created_at', '>=', now()->subMonth())->count();
        // 获取新增评论数量
        $newCommentsCount = Comment::where('created_at', '>=', now()->subMonth())->count();



        // 获取帖子举报数量
        //$reportedPostsCount = Report::where('target_type', 'post')->count();
        // 获取评论举报数量
        //$reportedCommentsCount = Report::where('target_type', 'comment')->count();
        // 获取用户举报数量
        //$reportedUsersCount = Report::where('target_type', 'user')->count();


        // 获取

        return response()->json([
            'code' => 200,
            'msg' => '概况数据获取成功',
            'data' => [
                'total_post_task' => $postsTaskCount,
                'total_comment_task' => $commentsTaskCount,
                'total_users' => $userCount,
            ],
        ]);
    }
}
