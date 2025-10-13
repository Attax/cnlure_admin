@extends('common.layouts')

@section('title', '评论管理')

@section('content')

<!-- 内容区 -->
<main x-data="commentPage">
    <!-- 筛选搜索 -->
    <div class="flex justify-between items-center gap-4 bg-white p-4 mb-4">
        <h1 class="text-2xl font-bold">评论管理</h1>
        <div class="flex space-x-2">
            <input type="text" placeholder="搜索评论内容"
                class="border rounded px-3 py-2 w-60" x-model="filters.keyword">
            <select class="border rounded px-3 py-2" x-model="filters.status">
                <option value="">全部状态</option>
                <option value="0">待审核</option>
                <option value="1">已通过</option>
                <option value="-1">待复核</option>
                <option value="-2">已拒绝</option>
            </select>
            <button @click="search()" :disabled="loading" class="px-4 py-2 bg-black text-white rounded hover:bg-gray-800 flex items-center justify-center gap-2">
                <span x-show="!loading">搜索</span>
                <span x-show="loading" class="animate-spin">⟳</span>
            </button>
        </div>
    </div>

    <!-- 数据表格 -->
    <div class="bg-white shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm min-w-[900px]">
                <thead class="bg-white">
                    <tr>
                        <th class="px-4 py-2 min-w-[80px]">ID</th>
                        <th class="px-4 py-2 min-w-[200px]">评论内容</th>
                        <th class="px-4 py-2 min-w-[150px]">图片/视频</th>
                        <th class="px-4 py-2 min-w-[120px]">用户</th>
                        <th class="px-4 py-2 min-w-[150px]">提交时间</th>
                        <th class="px-4 py-2 min-w-[100px]">审核状态</th>
                        <th class="px-4 py-2 min-w-[150px]">审核</th>
                        <th class="px-4 py-2 min-w-[120px]">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="comment in comments" :key="comment.id">
                        <tr class="border-t">
                            <td class="px-4 py-2" x-text="comment.id"></td>
                            <td class="px-4 py-2 truncate max-w-xs" x-text="comment.content"></td>
                            <td class="px-4 py-2 truncate max-w-xs">
                                <div class="picture-list">
                                    <template x-for="(image, index) in comment.images" :key="image.id">
                                        <div class="picture cursor-pointer">
                                            <img x-bind:src="image.url" alt="评论图片" class="w-12 h-12 rounded" @click="showImagePreview(image.url, index, comment)">
                                        </div>
                                    </template>
                                </div>
                                <template x-if="comment.video && comment.video.length > 0">
                                    <video :src="comment.video[0].url" alt="评论视频" class="w-12 h-12 rounded cursor-pointer" @click="showVideoPreview(comment.video[0].url, 0, comment)"></video>
                                </template>
                            </td>
                            <td class="px-4 py-2">
                                <a class="flex items-center" :href="`/users/${comment.author_id}`">
                                    <img x-bind:src="comment.user?.avatar || '/assets/images/avatar.png'" alt="用户头像" class="w-4 h-4 rounded-full ml-2" />
                                    <span class="text-sm truncate" x-text="comment.user?.nickname || '未知用户'"></span>
                                </a>
                            </td>
                            <td class="px-4 py-2" x-text="comment.created_at"></td>
                            <td class="px-4 py-2">
                                <template x-if="comment.audit_status === 0">
                                    <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded font-medium">待审核</span>
                                </template>
                                <template x-if="comment.audit_status === 1">
                                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded font-medium">已通过</span>
                                </template>
                                <template x-if="comment.audit_status === -1">
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded font-medium">待复核</span>
                                </template>
                                <template x-if="comment.audit_status === -2">
                                    <span class="px-3 py-1 bg-red-100 text-red-800 rounded font-medium">已驳回</span>
                                </template>
                            </td>
                            <td class="px-4 py-2 ">
                                <div class="flex flex-wrap gap-2">
                                    <button class="px-3 py-1 bg-blue-500 text-white rounded font-medium" @click="viewDetail(comment)">查看</button>
                                    <!-- 根据不同状态显示不同的操作按钮 -->
                                    <template x-if="comment.audit_status === 0 || comment.audit_status === -1">
                                        <button class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600 transition-colors" @click="approve(comment.id)">通过</button>
                                    </template>
                                    <template x-if="comment.audit_status === 0 || comment.audit_status === -1">
                                        <button class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 transition-colors" @click="openReject(comment)">驳回</button>
                                    </template>
                                </div>
                            </td>
                            <td class="px-4 py-2 text-right">
                                <!-- 删除按钮 -->
                                <template x-if="comment.status === 1">
                                    <button class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 transition-colors" @click="openDelete(comment)">删除评论</button>
                                </template>
                                <template x-if="comment.status === -1">
                                    <button class="px-3 py-1 bg-red-100 text-red-800 rounded font-medium" @click="undoDelete(comment)">恢复评论</button>
                                </template>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 分页 -->
    <div class="flex justify-end items-center gap-2 py-4">
        <button @click="prevPage" :disabled="page<=1 || loading" class="px-3 py-1 border rounded" x-show="page>1">上一页</button>
        <button @click="nextPage" :disabled="!hasMore || loading" class="px-3 py-1 border rounded" x-show="hasMore">下一页</button>
    </div>


    <!-- 查看详情弹窗 -->
    <div x-show="showDetail" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden" x-transition>
        <div class="bg-white p-6 rounded-lg shadow-xl w-[600px] space-y-3">
            <h2 class="font-semibold text-lg">评论详情</h2>
            <p class="text-gray-700" x-text="detail.content"></p>
            <div class="mb-4">
                <h3 class="font-semibold mt-4">审核历史</h3>
                <ul class="list-disc ml-5 text-sm text-gray-600">
                    <template x-for="h in detail.history">
                        <li x-text="h"></li>
                    </template>
                </ul>
            </div>
            <div class="text-right">
                <button @click="showDetail=false" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 transition-colors">关闭</button>
            </div>
        </div>
    </div>

    <!-- 拒绝理由弹窗 -->
    <div x-show="showReject" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden" x-transition>
        <div class="bg-white p-6 rounded-lg shadow-xl w-[400px] space-y-4">
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-lg">拒绝理由</h2>
                <button @click="showReject=false" class="text-gray-500 hover:text-gray-700">×</button>
            </div>
            <div>
                <p class="text-sm text-gray-600 mb-2">评论内容：<span x-text="rejectTarget?.content"></span></p>
                <p class="text-sm text-gray-600 mb-2">用户：<span x-text="rejectTarget?.user?.nickname || '未知用户'"></span></p>
            </div>
            <select class="border rounded w-full px-3 py-2" x-model="rejectReason">
                <option value="">请选择理由</option>
                <option value="0">违规内容</option>
                <option value="1">广告/垃圾信息</option>
                <option value="2">低质量内容</option>
            </select>
            <div class="mb-4">
                <h4 class="text-sm font-medium mb-1">拒绝原因详情：</h4>
                <div class="text-sm text-gray-500" x-show="rejectReason === '0'">违规内容 - 包含不符合社区规范的内容</div>
                <div class="text-sm text-gray-500" x-show="rejectReason === '1'">广告/垃圾信息 - 包含营销、推广或无关信息</div>
                <div class="text-sm text-gray-500" x-show="rejectReason === '2'">低质量内容 - 内容不完整或无意义</div>
            </div>
            <textarea class="border rounded w-full px-3 py-2" placeholder="备注（可选）" x-model="rejectNote"></textarea>
            <div class="text-right space-x-2">
                <button @click="showReject=false" class="px-4 py-2 border rounded hover:bg-gray-100 transition-colors">取消</button>
                <button @click="submitReject" :disabled="rejecting" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition-colors flex items-center gap-2 justify-center">
                    <span x-show="!rejecting">提交</span>
                    <span x-show="rejecting" class="animate-spin">⟳</span>
                </button>
            </div>
        </div>
    </div>

    <!-- 删除确认弹窗 -->
    <div x-show="showDelete" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden" x-transition>
        <div class="bg-white p-6 rounded-lg shadow-xl w-[400px] space-y-4">
            <h2 class="font-semibold text-lg">确认删除</h2>
            <p class="text-gray-700">确定要删除ID为 <span x-text="deleteTarget?.id"></span> 的评论吗？此操作不可恢复。</p>
            <div class="text-right space-x-2">
                <button @click="showDelete=false" class="px-4 py-2 bg-gray-200 rounded">取消</button>
                <button @click="submitDelete" class="px-4 py-2 bg-red-600 text-white rounded">确认删除</button>
            </div>
        </div>
    </div>
</main>
@endsection

@section('scripts')
@parent
<script>
    document.addEventListener('alpine:init', function() {
        Alpine.data('commentPage', function() {
            return {
                showDelete: false,
                deleteTarget: null,
                comments: [],
                page: 1,
                pageSize: 10,
                loading: false,
                hasMore: true,
                // 用户操作的筛选条件（未点击搜索按钮时不生效）
                filters: {
                    keyword: '',
                    status: ''
                },

                // 生效的筛选条件，只有点击搜索时才会更新
                activeFilters: {
                    keyword: '',
                    status: ''
                },

                showDetail: false,
                detail: {
                    content: '',
                    history: []
                },
                showReject: false,
                rejectTarget: null,
                rejectReason: '',
                rejectNote: '',
                rejecting: false,
                deleting: false,
                approving: false,

                // 获取列表
                fetchComments: async function() {
                    // 使用活跃的筛选条件
                    const params = new URLSearchParams({
                        page: this.page,
                        pageSize: this.pageSize
                    });

                    // 只添加非空的筛选条件
                    if (this.activeFilters.keyword && this.activeFilters.keyword.trim() !== '') {
                        params.append('keyword', this.activeFilters.keyword);
                    }
                    if (this.activeFilters.status !== undefined && this.activeFilters.status !== null && this.activeFilters.status !== '') {
                        params.append('status', this.activeFilters.status);
                    }
                    try {
                        // API路径已正确使用/api/comments
                        const {
                            code,
                            data,
                            pagination
                        } = await fetch(`/api/comments?${params.toString()}`).then(res => res.json());
                        this.comments = data || [];
                        this.hasMore = pagination.has_more || false;

                    } catch (e) {
                        console.error("获取评论失败", e);
                        alert('获取评论失败: ' + e.message);
                    } finally {
                        this.loading = false;
                    }
                },

                search: function() {
                    // 点击搜索时，将当前筛选条件保存到活跃筛选条件中
                    this.activeFilters = Object.assign({}, this.filters);
                    this.page = 1;
                    this.loading = true;
                    this.fetchComments();
                },

                statusText: function(status) {
                    return {
                        '-1': '已拒绝',
                        '0': '待审核',
                        '1': '已通过',
                    } [status] || '未知';
                },

                viewDetail: function(comment) {
                    this.detail = {
                        content: comment.content,
                        history: ['2025-09-27 管理员A 提交审核', '2025-09-28 管理员B 已通过']
                    };
                    this.showDetail = true;
                    document.querySelector('[x-show="showDetail"]').classList.remove('hidden');
                },

                approve: async function(id) {
                    if (this.approving) return;
                    if (!confirm('确定要通过这条评论吗？')) return;
                    this.approving = true;
                    try {
                        // API路径已正确使用/api/comments/{id}/approve
                        const response = await fetch(`/api/comments/${id}/audit`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                status: 1
                            })
                        });

                        const data = await response.json();
                        if (data.code === 200) {
                            alert(`评论 ${id} 已通过`);
                            // 更新本地数据
                            const index = this.comments.findIndex(function(c) {
                                return c.id === id;
                            }, this);
                            if (index !== -1) {
                                this.comments[index].audit_status = 1;
                            }
                        } else {
                            alert('审核失败: ' + (data.msg || '未知错误'));
                        }
                    } catch (e) {
                        console.error('审核请求失败', e);
                        alert('审核请求失败，请稍后重试');
                    } finally {
                        this.approving = false;
                    }
                },

                openReject: function(comment) {
                    this.rejectTarget = comment;
                    this.rejectReason = '';
                    this.rejectNote = '';
                    this.showReject = true;
                    document.querySelector('[x-show="showReject"]').classList.remove('hidden');
                },

                // 驳回评论
                submitReject: async function() {
                    if (!this.rejectReason) {
                        alert('请选择拒绝理由');
                        return;
                    }

                    if (this.rejecting) return;

                    this.rejecting = true;
                    try {
                        // API路径已正确使用/api/comments/{id}/reject
                        const response = await fetch(`/api/comments/${this.rejectTarget.id}/audit`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                status: -2,
                                reason: this.rejectReason,
                                note: this.rejectNote
                            })
                        });

                        const data = await response.json();
                        if (data.code === 200) {
                            alert(`评论 ${this.rejectTarget.id} 已拒绝，理由：${this.rejectReason}`);
                            // 更新本地数据
                            const index = this.comments.findIndex(function(c) {
                                return c.id === this.rejectTarget.id;
                            }, this);
                            if (index !== -1) {
                                this.comments[index].audit_status = -2;
                                this.comments[index].reject_reason = this.rejectReason;
                            }

                            this.showReject = false;
                            this.rejectReason = '';
                            this.rejectNote = '';
                        } else {
                            alert('拒绝失败: ' + (data.msg || '未知错误'));
                        }
                    } catch (e) {
                        console.error('拒绝请求失败', e);
                        alert('拒绝请求失败，请稍后重试');
                    } finally {
                        this.rejecting = false;
                    }
                },


                openDelete: function(comment) {
                    this.deleteTarget = comment;
                    this.showDelete = true;
                    document.querySelector('[x-show="showDelete"]').classList.remove('hidden');
                },

                submitDelete: async function() {
                    if (this.deleting || !this.deleteTarget) return;

                    this.deleting = true;
                    try {
                        const response = await fetch(`/api/comments/${this.deleteTarget.id}`, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json'
                            }
                        });

                        const data = await response.json();
                        if (data.code === 200) {
                            alert(`评论 ${this.deleteTarget.id} 已成功删除`);

                            // 更新本地数据
                            const index = this.comments.findIndex(function(c) {
                                return c.id === this.deleteTarget.id;
                            }, this);
                            if (index !== -1) {
                                this.comments[index].status = -1;
                            }

                            this.showDelete = false;
                            this.deleteTarget = null;

                        } else {
                            alert('删除失败: ' + (data.msg || '未知错误'));
                        }
                    } catch (e) {
                        console.error('删除请求失败', e);
                        alert('删除请求失败，请稍后重试');
                    } finally {
                        this.deleting = false;
                    }
                },

                // 恢复评论
                async undoDelete(comment) {
                    if (this.loading) return;

                    this.loading = true;
                    try {
                        const response = await fetch(`/api/comments/${comment.id}/restore`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json'
                            }
                        });

                        const data = await response.json();
                        if (data.code === 200) {
                            alert(`评论 ${comment.id} 已成功恢复`);
                            // 更新本地数据
                            const index = this.comments.findIndex(function(c) {
                                return c.id === comment.id;
                            }, this);
                            if (index !== -1) {
                                this.comments[index].status = 1;
                            }
                        } else {
                            alert('恢复失败: ' + (data.msg || '未知错误'));
                        }
                    } catch (e) {
                        console.error('恢复请求失败', e);
                        alert('恢复请求失败，请稍后重试');
                    } finally {
                        this.loading = false;
                    }
                },


                prevPage: function() {
                    if (this.page > 1 && !this.loading) {
                        this.page--;
                        this.loading = true;
                        this.fetchComments();
                    }
                },

                nextPage: function() {
                    if (this.hasMore && !this.loading) {
                        this.page++;
                        this.loading = true;
                        this.fetchComments();
                    }
                },

                init: function() {
                    // 初始化时，将筛选条件复制到活跃筛选条件
                    this.activeFilters = Object.assign({}, this.filters);
                    this.fetchComments();
                }
            };
        });
    });
</script>
@endsection