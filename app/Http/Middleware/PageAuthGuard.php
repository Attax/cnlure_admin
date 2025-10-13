<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PageAuthGuard
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 从session中判断是否登录

        if ($request->session()->get('user_role') === 'admin') {
            // 用户是管理员，允许请求继续
            return $next($request);
        } else {
            // 用户不是管理员，重定向到其他页面或返回错误
            return redirect('/login'); // 或者其他页面
        }
    }
}
