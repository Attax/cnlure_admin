@extends('common.layouts')

@section('title', '用户举报管理')

@section('content')

<!-- 主体 -->
<main x-data="userPage">

    <!-- 筛选搜索 -->
    <div class="flex justify-between items-center gap-4 bg-white p-4 mb-4">
        <h1 class="text-2xl font-bold">用户举报管理</h1>
        <div class="flex space-x-2">
            <input type="text" placeholder="搜索用户名或举报人" x-model="filters.keyword"
                class="border border-gray-300 rounded px-3 py-2 w-64 focus:outline-none focus:ring-1 focus:ring-black">
            <select x-model="filters.status" class="border border-gray-300 rounded px-2 py-2 focus:outline-none focus:ring-1 focus:ring-black">
                <option value="">全部状态</option>
                <option value="pending">待处理</option>
                <option value="resolved">已处理</option>
            </select>
            <button @click="loadReports(1)" class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800">搜索</button>
        </div>
    </div>

    <!-- 表格 -->
    <div class="bg-white shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-white">
                <tr>
                    <th class="px-4 py-2">ID</th>
                    <th class="px-4 py-2">被举报用户</th>
                    <th class="px-4 py-2">举报原因</th>
                    <th class="px-4 py-2">举报人</th>
                    <th class="px-4 py-2">状态</th>
                    <th class="px-4 py-2">操作</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <template x-for="report in reports" :key="report.id">
                    <tr>
                        <td class="px-4 py-2 text-sm" x-text="report.id"></td>
                        <td class="px-4 py-2 text-sm text-black underline cursor-pointer" @click="openDetail(report)" x-text="report.user_name"></td>
                        <td class="px-4 py-2 text-sm" x-text="report.reason"></td>
                        <td class="px-4 py-2 text-sm" x-text="report.reporter_name"></td>
                        <td class="px-4 py-2 text-sm" :class="report.status==='pending'?'text-yellow-600':'text-green-600'" x-text="report.status==='pending'?'待处理':'已处理'"></td>
                        <td class="px-4 py-2 text-center space-x-2">
                            <button class="text-black hover:underline" @click="openDetail(report)">查看</button>
                            <button class="text-white bg-black px-2 py-1 rounded hover:bg-gray-800" @click="approve(report.id)">通过</button>
                            <button class="text-white bg-red-600 px-2 py-1 rounded hover:bg-red-500" @click="openReject(report)">拒绝</button>
                            <button class="text-white bg-gray-700 px-2 py-1 rounded hover:bg-gray-600" @click="suspendUser(report.user_id)">封禁</button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <!-- 分页 -->
    <div class="flex justify-between items-center mt-4 text-sm text-gray-500">
        <div>共 <span x-text="pagination.total"></span> 条</div>
        <div class="space-x-2">
            <button class="px-3 py-1 border rounded" :disabled="pagination.page===1" @click="loadReports(pagination.page-1)">上一页</button>
            <span>第 <span x-text="pagination.page"></span> / <span x-text="pagination.pages"></span> 页</span>
            <button class="px-3 py-1 border rounded" :disabled="pagination.page===pagination.pages" @click="loadReports(pagination.page+1)">下一页</button>
        </div>
    </div>


    <!-- 详情弹窗 -->
    <div x-show="showDetail" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white w-2/3 rounded-lg shadow-lg p-6 relative">
            <h2 class="text-xl font-bold mb-4">举报详情</h2>
            <p><span class="font-medium">被举报用户：</span> <span x-text="currentReport.user_name"></span></p>
            <p><span class="font-medium">举报原因：</span> <span x-text="currentReport.reason"></span></p>
            <p><span class="font-medium">举报历史：</span></p>
            <ul class="list-disc pl-6 text-sm text-gray-700">
                <template x-for="h in currentReport.history">
                    <li x-text="h"></li>
                </template>
            </ul>
            <button class="absolute top-2 right-2 text-gray-500" @click="showDetail=false">✕</button>
        </div>
    </div>

    <!-- 拒绝弹窗 -->
    <div x-show="showReject" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white w-1/3 rounded-lg shadow-lg p-6 relative">
            <h2 class="text-xl font-bold mb-4">拒绝举报</h2>
            <p class="mb-2 text-sm text-gray-600">请选择拒绝理由：</p>
            <select class="border w-full px-2 py-2 rounded mb-4" x-model="rejectReason">
                <option value="">请选择</option>
                <option value="无效举报">无效举报</option>
                <option value="行为不违规">行为不违规</option>
                <option value="重复举报">重复举报</option>
            </select>
            <div class="flex justify-end space-x-2">
                <button class="px-4 py-2 border rounded" @click="showReject=false">取消</button>
                <button class="px-4 py-2 bg-red-600 text-white rounded" :disabled="!rejectReason" @click="submitReject">确认拒绝</button>
            </div>
            <button class="absolute top-2 right-2 text-gray-500" @click="showReject=false">✕</button>
        </div>
    </div>

</main>

@endsection

@section('scripts')
@parent
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('userPage', () => ({
            reports: [],
            pagination: {
                page: 1,
                pages: 1,
                total: 0
            },
            filters: {
                keyword: '',
                status: ''
            },
            showDetail: false,
            currentReport: {},
            showReject: false,
            rejectReason: '',
            rejectTarget: null,

            async loadReports(page = 1) {
                this.pagination.page = page;
                const query = new URLSearchParams({
                    ...this.filters,
                    page
                });
                const res = await fetch(`/api/report/users?${query}`);
                const data = await res.json();
                this.reports = data.items;
                this.pagination = data.pagination;
            },

            openDetail(report) {
                this.currentReport = report;
                this.showDetail = true;
            },
            approve(id) {
                fetch(`/api/report/users/${id}/approve`, {
                    method: 'POST'
                }).then(() => this.loadReports(this.pagination.page));
            },
            openReject(report) {
                this.rejectTarget = report;
                this.showReject = true;
            },
            submitReject() {
                if (!this.rejectReason) return;
                fetch(`/api/report/users/${this.rejectTarget.id}/reject`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            reason: this.rejectReason
                        })
                    })
                    .then(() => {
                        this.showReject = false;
                        this.rejectReason = '';
                        this.loadReports(this.pagination.page);
                    });
            },

            suspendUser(userId) {
                if (!confirm('确认封禁该用户吗？')) return;
                fetch(`/api/users/${userId}/suspend`, {
                    method: 'POST'
                }).then(() => this.loadReports(this.pagination.page));
            },

            init() {
                this.loadReports();
            }
        }));
    })
</script>
@endsection