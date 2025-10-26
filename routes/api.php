<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FishingSpotController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\ApiAuthGuard;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// API路由组 - 统一应用API认证中间件

// 概况
Route::get('/overview', [HomeController::class, 'overview']);


// 用户相关接口
Route::prefix('users')->group(function () {

    Route::get('', [UserController::class, 'getList']);
    Route::get('/detail/{id}', [UserController::class, 'detail']);
    Route::post('/edit/{id}', [UserController::class, 'edit']);
    Route::post('/ban/{id}', [UserController::class, 'ban']);
    Route::post('/unban/{id}', [UserController::class, 'unban']);


    // 对用户的账号进行操作（禁用账号、删除账号等操作）
    Route::put('/{id}/ban', [AccountController::class, 'ban']);
    // 删号
    Route::delete('/{id}', [AccountController::class, 'destroy']);

    // 认证信息
    Route::get('/{id}/verify', [AccountController::class, 'verfiyInfo']);
    // 审核认证信息
    Route::post('/{id}/verify/audit', [AccountController::class, 'auditVerifyInfo']);
    // 认证信息编辑
    Route::put('/{id}/verify', [AccountController::class, 'updateVerifyInfo']);
});

// 用户封禁历史接口
Route::get('/users/{userId}/status-history', [UserController::class, 'getStatusHistory']);

// 帖子相关接口
Route::prefix('posts')->group(function () {
    // 帖子列表
    Route::get('', [PostController::class, 'list']);
    // 帖子详情
    Route::get('/{id}', [PostController::class, 'item']);
    // 帖子审核历史
    Route::get('/{id}/audit-history', [PostController::class, 'getAuditHistory']);
    // 审核帖子
    Route::put('/{id}/audit', [PostController::class, 'audit']);
    // 删除帖子
    Route::delete('/{id}', [PostController::class, 'destroy']);
    // 恢复帖子
    Route::put('/{id}/restore', [PostController::class, 'restore']);
});



// 评论相关接口
Route::prefix('comments')->group(function () {
    // 评论列表
    Route::get('', [CommentController::class, 'list']);
    // 评论详情
    Route::get('/{id}', [CommentController::class, 'item']);

    // 评论审核历史
    Route::get('/{id}/audit-history', [CommentController::class, 'getAuditHistory']);
    // 审核评论
    Route::put('/{id}/audit', [CommentController::class, 'audit']);

    // 删除评论
    Route::delete('/{id}', [CommentController::class, 'destroy']);

    // 恢复评论
    Route::put('/{id}/restore', [CommentController::class, 'restore']);
});


// 钓场相关接口
Route::group(['prefix' => 'fishing-spots'], function () {
    Route::get('/', [FishingSpotController::class, 'getList']);
    Route::get('/{id}', [FishingSpotController::class, 'item']);
    Route::post('/', [FishingSpotController::class, 'store']);
    Route::put('/{id}', [FishingSpotController::class, 'update']);
    Route::delete('/{id}', [FishingSpotController::class, 'destroy']);
});
