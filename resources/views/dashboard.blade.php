@extends('common.layouts')

@section('title')
<h1 class="text-2xl font-bold">仪表盘</h1>
@endsection

@section('content')
<main x-data="dashboard">
    <h1 class="text-3xl font-bold mb-6">仪表盘</h1>

    <!-- 加载状态 -->
    <div x-show="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-black"></div>
    </div>

    <!-- 错误状态 -->
    <div x-show="error && !loading" class="bg-red-50 border border-red-200 p-4 rounded mb-6">
        <p class="text-red-600" x-text="error"></p>
    </div>

    <!-- 数据卡片 -->
    <div x-show="!loading && !error" class="grid grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded shadow hover:shadow-md transition-shadow cursor-pointer" @click="navigateTo('/posts')">
            <p class="text-gray-500">待审核帖子</p>
            <p class="text-2xl font-bold" x-text="stats.total_post_task || 0"></p>
        </div>
        <div class="bg-white p-6 rounded shadow hover:shadow-md transition-shadow cursor-pointer" @click="navigateTo('/comments')">
            <p class="text-gray-500">待审核评论</p>
            <p class="text-2xl font-bold" x-text="stats.total_comment_task || 0"></p>
        </div>
        <div class="bg-white p-6 rounded shadow hover:shadow-md transition-shadow cursor-pointer" @click="navigateTo('/users')">
            <p class="text-gray-500">用户数</p>
            <p class="text-2xl font-bold" x-text="stats.total_users || 0"></p>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('dashboard', () => ({
                loading: true,
                error: '',
                stats: {},

                init() {
                    this.fetchStats();
                },

                async fetchStats() {
                    try {
                        this.loading = true;
                        this.error = '';

                        const response = await fetch('/api/overview');
                        const data = await response.json();

                        if (data.code === 200) {
                            this.stats = data.data || {};
                        } else {
                            this.error = data.msg || '获取数据失败';
                        }
                    } catch (e) {
                        console.error('获取仪表盘数据失败', e);
                        this.error = '获取数据失败，请稍后重试';
                    } finally {
                        this.loading = false;
                    }
                },

                navigateTo(url) {
                    window.location.href = url;
                }
            }));
        });
    </script>
</main>
@endsection