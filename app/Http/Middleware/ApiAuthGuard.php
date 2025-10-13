<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthGuard
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 调试信息
        error_log('ApiAuthGuard: Request URL - ' . $request->url());
        error_log('ApiAuthGuard: Session user_role - ' . ($request->session()->get('user_role') ?? 'null'));
        
        // 从session中判断是否登录
        if ($request->session()->get('user_role') === 'admin') {
            // 用户是管理员，允许请求继续
            error_log('ApiAuthGuard: Access granted');
            return $next($request);
        } else {
            // 用户不是管理员，返回错误JSON
            error_log('ApiAuthGuard: Access denied');
            return response()->json([
                'code' => 403,
                'message' => '权限不足',
            ]);
        }
    }
}
