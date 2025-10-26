<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FishingSpotController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;


use App\Http\Middleware\PageAuthGuard;
use App\Http\Middleware\ApiAuthGuard;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
});

Route::get('/login', function () {
    return view('auth');
});

// 用户管理
Route::prefix('users')->group(function () {
    Route::get('', [UserController::class, 'list']);
    // 获取用户状态变更历史
    Route::get('/{userId}/status-history', [UserController::class, 'getStatusHistory'])->name('users.status-history');
});



// 帖子管理

Route::get('/posts', [PostController::class, 'listPage']);
// 帖子详情
Route::get('/posts/{id}', [PostController::class, 'item']);
// 帖子评论
Route::get('/posts/{id}/comments', [PostController::class, 'itemComments']);



// 帖子图片审核
Route::get('/posts/images', [PostController::class, 'imagePostList']);





// 评论管理

Route::get('/comments', [CommentController::class, 'listPage']);
// 评论举报
Route::get('/comments/reports', [CommentController::class, 'reportList']);


// 评论详情
Route::get('/comments/{id}', [CommentController::class, 'item']);



// 举报功能

Route::get('/report/posts', [ReportController::class, 'postList']);
Route::get('/report/comments', [ReportController::class, 'commentList']);
Route::get('/report/users', [ReportController::class, 'userList']);



// 钓场管理页面
Route::get('/fishing-spots', [FishingSpotController::class, 'list']);
