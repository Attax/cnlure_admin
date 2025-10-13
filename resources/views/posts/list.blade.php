@extends('common.layouts')

@section('title', '帖子管理')

@section('content')

<!-- 内容区 -->
<main x-data="postPage">
    <!-- 筛选搜索 -->
    <div class="flex justify-between items-center gap-4 bg-white p-4 mb-4">
        <h1 class="text-2xl font-bold">帖子管理</h1>
        <div class="flex space-x-2">
            <input type="text" placeholder="搜索帖子标题/内容"
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
                        <th class="px-4 py-2 min-w-[200px]">内容摘要</th>
                        <th class="px-4 py-2 min-w-[150px]">图片/视频</th>
                        <th class="px-4 py-2 min-w-[120px]">用户</th>
                        <th class="px-4 py-2 min-w-[150px]">提交时间</th>
                        <th class="px-4 py-2 min-w-[100px]">审核状态</th>
                        <th class="px-4 py-2 min-w-[150px]">审核</th>
                        <th class="px-4 py-2 min-w-[120px]">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="post in posts" :key="post.id">
                        <tr class="border-t">
                            <td class="px-4 py-2" x-text="post.id"></td>
                            <td class="px-4 py-2 truncate max-w-xs" x-text="post.content"></td>
                            <td class="px-4 py-2 truncate max-w-xs">
                                <div class="picture-list">
                                    <template x-for="(image, index) in post.images" :key="image.id">
                                        <div class="picture cursor-pointer">
                                            <img x-bind:src="image.url" alt="帖子图片" class="w-12 h-12 rounded" @click="showImagePreview(image.url, index, post)">
                                        </div>
                                    </template>
                                </div>
                                <template x-if="post.videos && post.videos.length > 0">
                                    <video :src="post.videos[0].url" alt="帖子视频" class="w-12 h-12 rounded cursor-pointer" @click="showVideoPreview(post.videos[0].url, 0, post)"></video>
                                </template>
                            </td>
                            <td class="px-4 py-2">
                                <a class="flex items-center" :href="`/users/${post.user_id}`">
                                    <img x-bind:src="post.user?.avatar || '/assets/images/avatar.png'" alt="用户头像" class="w-4 h-4 rounded-full ml-2" />
                                    <span class="text-sm truncate" x-text="post.user?.nickname || '未知用户'"></span>
                                </a>
                            </td>
                            <td class="px-4 py-2" x-text="post.created_at"></td>
                            <td class="px-4 py-2">
                                <template x-if="post.audit_status === 0">
                                    <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded font-medium">待审核</span>
                                </template>
                                <template x-if="post.audit_status === 1">
                                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded font-medium">已通过</span>
                                </template>
                                <template x-if="post.audit_status === -1">
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded font-medium">待复核</span>
                                </template>
                                <template x-if="post.audit_status === -2">
                                    <span class="px-3 py-1 bg-red-100 text-red-800 rounded font-medium">已驳回</span>
                                </template>
                            </td>
                            <td class="px-4 py-2 ">
                                <div class="flex flex-wrap gap-2">
                                    <button class="px-3 py-1 bg-blue-500 text-white rounded font-medium" @click="viewDetail(post)">查看</button>
                                    <!-- 根据不同状态显示不同的操作按钮 -->
                                    <template x-if="post.audit_status === 0 || post.audit_status === -1">
                                        <button class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600 transition-colors" @click="approve(post.id)">通过</button>
                                    </template>
                                    <template x-if="post.audit_status === 0 || post.audit_status === -1">
                                        <button class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 transition-colors" @click="openReject(post)">驳回</button>
                                    </template>
                                </div>
                            </td>
                            <td class="px-4 py-2 text-right">
                                <!-- 删除按钮 -->
                                <template x-if="post.status === 1">
                                    <button class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 transition-colors" @click="openDelete(post)">删除帖子</button>
                                </template>
                                <template x-if="post.status === -1">
                                    <button class="px-3 py-1 bg-red-100 text-red-800 rounded font-medium" @click="undoDelete(post)">恢复帖子</button>
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
        <button @click="prevPage" :disabled="page<=1" class="px-3 py-1 border rounded" x-show="page>1">上一页</button>
        <button @click="nextPage" :disabled="!hasMore" class="px-3 py-1 border rounded" x-show="hasMore">下一页</button>
    </div>


    <!-- 查看详情弹窗 -->
    <div x-show="showDetail" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden" x-transition>
        <div class="bg-white p-6 rounded-lg shadow-xl w-[600px] space-y-4">
            <h2 class="font-semibold text-lg">帖子详情</h2>
            <p class="text-gray-700" x-text="detail.content"></p>
            <div>
                <h3 class="font-semibold mt-4">审核历史</h3>
                <ul class="list-disc ml-5 text-sm text-gray-600">
                    <template x-for="h in detail.history">
                        <li x-text="h"></li>
                    </template>
                </ul>
            </div>
            <div class="text-right">
                <button @click="showDetail=false" class="px-4 py-2 bg-gray-200 rounded">关闭</button>
            </div>
        </div>
    </div>

    <!-- 图片预览弹窗 -->
    <div x-show="showImageModal" class="fixed inset-0 bg-black/70 flex items-center justify-center z-50 overflow-auto hidden" x-transition
        @click="showImageModal=false"
        @keydown.escape="showImageModal=false"
        x-init="() => document.addEventListener('keydown', (e) => { if (e.key === 'Escape') showImageModal = false })">
        <div class="bg-white rounded-lg shadow-xl w-[90vw] max-w-[70vw] p-6" @click.stop>
            <!-- 头部区域 -->
            <div class="mb-4 flex justify-between items-center">
                <!-- 导航信息 -->
                <div class="bg-black/60 text-white px-4 py-2 rounded-full text-sm">
                    <span x-text="currentImageIndex + 1"></span> / <span x-text="allImageUrls.length"></span>
                </div>
                <!-- 关闭按钮 -->
                <button @click="showImageModal=false" class="text-black text-2xl hover:text-gray-600 transition-colors">
                    ×
                </button>
            </div>

            <!-- 图片预览区域 -->
            <div class="relative">
                <!-- 图片容器 - 确保图片完全显示 -->
                <div class="flex justify-center items-center py-4">
                    <img :src="previewImageUrl" alt="预览图片" class="max-w-full max-h-[70vh] h-auto object-contain">
                </div>

                <!-- 切换按钮组 -->
                <div class="flex justify-between mt-2">
                    <!-- 左侧切换按钮 -->
                    <button
                        @click="prevImage"
                        :disabled="currentImageIndex === 0"
                        class="bg-black/60 text-white px-6 py-3 rounded-full flex items-center justify-center hover:bg-black/80 transition-colors"
                        :class="{ 'opacity-50 cursor-not-allowed': currentImageIndex === 0 }">
                        上一张
                    </button>

                    <!-- 右侧切换按钮 -->
                    <button
                        @click="nextImage"
                        :disabled="currentImageIndex >= allImageUrls.length - 1"
                        class="bg-black/60 text-white px-6 py-3 rounded-full flex items-center justify-center hover:bg-black/80 transition-colors"
                        :class="{ 'opacity-50 cursor-not-allowed': currentImageIndex >= allImageUrls.length - 1 }">
                        下一张
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 视频预览弹窗 -->
    <div x-show="showVideoModal" class="fixed inset-0 bg-black/70 flex items-center justify-center z-50 overflow-auto hidden" x-transition
        @click="showVideoModal=false"
        @keydown.escape="showVideoModal=false"
        x-init="() => document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && showVideoModal) showVideoModal = false })">
        <div class="bg-white rounded-lg shadow-xl w-[90vw] max-w-[70vw] p-6" @click.stop>
            <!-- 头部区域 -->
            <div class="mb-4 flex justify-between items-center">
                <!-- 导航信息 -->
                <div class="bg-black/60 text-white px-4 py-2 rounded-full text-sm">
                    <span x-text="currentVideoIndex + 1"></span> / <span x-text="allVideoUrls.length"></span>
                </div>
                <!-- 关闭按钮 -->
                <button @click="showVideoModal=false" class="text-black text-2xl hover:text-gray-600 transition-colors">
                    ×
                </button>
            </div>

            <!-- 视频预览区域 -->
            <div class="relative">
                <!-- 视频容器 - 确保视频完全显示 -->
                <div class="flex justify-center items-center py-4">
                    <video id="previewVideo" :src="previewVideoUrl" alt="预览视频" class="max-w-full max-h-[70vh] h-auto object-contain" controls></video>
                </div>

                <!-- 切换按钮组 -->
                <div class="flex justify-between mt-2">
                    <!-- 左侧切换按钮 -->
                    <button
                        @click="prevVideo"
                        :disabled="currentVideoIndex === 0"
                        class="bg-black/60 text-white px-6 py-3 rounded-full flex items-center justify-center hover:bg-black/80 transition-colors"
                        :class="{ 'opacity-50 cursor-not-allowed': currentVideoIndex === 0 }">
                        上一个
                    </button>

                    <!-- 右侧切换按钮 -->
                    <button
                        @click="nextVideo"
                        :disabled="currentVideoIndex >= allVideoUrls.length - 1"
                        class="bg-black/60 text-white px-6 py-3 rounded-full flex items-center justify-center hover:bg-black/80 transition-colors"
                        :class="{ 'opacity-50 cursor-not-allowed': currentVideoIndex >= allVideoUrls.length - 1 }">
                        下一个
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 拒绝理由弹窗 -->
    <div x-show="showReject" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden" x-transition>
        <div class="bg-white p-6 rounded-lg shadow-xl w-[400px] space-y-4">
            <h2 class="font-semibold text-lg">拒绝理由</h2>
            <select class="border rounded w-full px-3 py-2" x-model="rejectReason">
                <option value="">请选择理由</option>
                <option value="违规内容">违规内容</option>
                <option value="广告/垃圾信息">广告/垃圾信息</option>
                <option value="低质量内容">低质量内容</option>
            </select>
            <textarea class="border rounded w-full px-3 py-2" placeholder="备注（可选）" x-model="rejectNote"></textarea>
            <div class="text-right space-x-2">
                <button @click="showReject=false" class="px-4 py-2 bg-gray-200 rounded">取消</button>
                <button @click="submitReject" class="px-4 py-2 bg-red-600 text-white rounded">提交</button>
            </div>
        </div>
    </div>

    <!-- 删除确认弹窗 -->
    <div x-show="showDelete" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden" x-transition>
        <div class="bg-white p-6 rounded-lg shadow-xl w-[400px] space-y-4">
            <h2 class="font-semibold text-lg">确认删除</h2>
            <p class="text-gray-700">确定要删除ID为 <span x-text="deleteTarget?.id"></span> 的帖子吗？此操作不可恢复。</p>
            <div class="text-right space-x-2">
                <button @click="showDelete=false" class="px-4 py-2 bg-gray-200 rounded">取消</button>
                <button @click="submitDelete" class="px-4 py-2 bg-red-600 text-white rounded">确认删除</button>
            </div>
        </div>
    </div>
</main>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('postPage', () => ({
            posts: [],
            page: 1,
            pageSize: 10,
            hasMore: true,
            loading: false,
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
            showDelete: false,
            deleteTarget: null,
            showImageModal: false,
            previewImageUrl: '',
            currentImageIndex: 0,
            currentPostId: null,
            allImageUrls: [],

            showVideoModal: false,
            previewVideoUrl: '',
            currentVideoIndex: 0,
            allVideoUrls: [],

            // 图片预览
            showImagePreview(url, index, post) {
                // 收集当前帖子的所有图片URL
                this.currentPostId = post.id;
                this.currentImageIndex = index;
                this.allImageUrls = post.images.map(img => img.url);
                this.previewImageUrl = url;
                this.showImageModal = true;
                document.querySelector('[x-show="showImageModal"]').classList.remove('hidden');
            },

            showVideoPreview(url, index, post) {
                // 收集当前帖子的所有视频URL
                this.currentPostId = post.id;
                this.currentVideoIndex = index;
                this.allVideoUrls = post.post_video.map(video => video.url);
                this.previewVideoUrl = url;
                this.showVideoModal = true;
                document.querySelector('[x-show="showVideoModal"]').classList.remove('hidden');
                // 当视频预览窗口打开时，自动播放视频
                setTimeout(() => {
                    const videoElement = document.getElementById('previewVideo');
                    if (videoElement) {
                        videoElement.play().catch(e => console.log('无法自动播放视频:', e));
                    }
                }, 100);
            },

            // 下一张图片
            nextImage() {
                if (this.currentImageIndex < this.allImageUrls.length - 1) {
                    this.currentImageIndex++;
                    this.previewImageUrl = this.allImageUrls[this.currentImageIndex];
                }
            },

            prevImage() {
                if (this.currentImageIndex > 0) {
                    this.currentImageIndex--;
                    this.previewImageUrl = this.allImageUrls[this.currentImageIndex];
                }
            },

            prevVideo() {
                if (this.currentVideoIndex > 0) {
                    this.currentVideoIndex--;
                    this.previewVideoUrl = this.allVideoUrls[this.currentVideoIndex];
                    // 切换视频后尝试播放
                    setTimeout(() => {
                        const videoElement = document.getElementById('previewVideo');
                        if (videoElement) {
                            videoElement.load();
                            videoElement.play().catch(e => console.log('无法自动播放视频:', e));
                        }
                    }, 100);
                }
            },

            nextVideo() {
                if (this.currentVideoIndex < this.allVideoUrls.length - 1) {
                    this.currentVideoIndex++;
                    this.previewVideoUrl = this.allVideoUrls[this.currentVideoIndex];
                    // 切换视频后尝试播放
                    setTimeout(() => {
                        const videoElement = document.getElementById('previewVideo');
                        if (videoElement) {
                            videoElement.load();
                            videoElement.play().catch(e => console.log('无法自动播放视频:', e));
                        }
                    }, 100);
                }
            },

            // 获取列表
            async fetchPosts() {
                // 使用活跃的筛选条件，而不是直接使用this.filters
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
                    // API路径已正确使用/api/posts
                    const {
                        code,
                        data,
                        pagination
                    } = await fetch(`/api/posts?${params.toString()}`).then(res => res.json());
                    this.posts = data || [];
                    this.hasMore = pagination.has_more || false;

                } catch (e) {
                    console.error("获取帖子失败", e);
                } finally {
                    this.loading = false;
                }
            },
            search() {
                // 点击搜索时，将当前筛选条件保存到活跃筛选条件中
                this.activeFilters = Object.assign({}, this.filters);
                this.page = 1;
                this.loading = true;
                this.fetchPosts();
            },
            statusText(status) {
                return {
                    '-2': '已拒绝',
                    '-1': '待复核',
                    '0': '待审核',
                    '1': '审核通过',
                } [status] || '未知'
            },
            async viewDetail(post) {
                this.loading = true;
                try {
                    // 并发请求帖子详情和审核历史 (api.php中定义的路由自动有/api前缀)
                    const [detailResponse, historyResponse] = await Promise.all([
                        fetch(`/api/posts/${post.id}`).then(res => res.json()),
                        fetch(`/api/posts/${post.id}/audit-history`).then(res => res.json())
                    ]);

                    if (detailResponse.code === 200 && historyResponse.code === 200) {
                        this.detail = {
                            content: detailResponse.data.content || detailResponse.data.title || '暂无内容',
                            history: historyResponse.data || []
                        };
                        this.showDetail = true;
                        document.querySelector('[x-show="showDetail"]').classList.remove('hidden');
                    } else {
                        alert('获取详情失败');
                    }
                } catch (error) {
                    console.error('获取帖子详情失败', error);
                    alert('获取详情失败，请稍后重试');
                } finally {
                    this.loading = false;
                }
            },
            // 审核通过
            async approve(id) {
                if (this.loading) return;

                this.loading = true;
                try {
                    // API路径已正确使用/api/posts/{id}/audit
                    const response = await fetch(`/api/posts/${id}/audit`, {
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
                        alert(`帖子 ${id} 审核通过`);
                        // 更新本地数据
                        const index = this.posts.findIndex(function(c) {
                            return c.id === id;
                        }, this);
                        if (index !== -1) {
                            this.posts[index].audit_status = 1;
                        }
                    } else {
                        alert('审核失败: ' + (data.msg || '未知错误'));
                    }
                } catch (e) {
                    console.error('审核请求失败', e);
                    alert('审核请求失败，请稍后重试');
                } finally {
                    this.loading = false;
                }
            },
            openReject(post) {
                this.rejectTarget = post;
                this.rejectReason = '';
                this.rejectNote = '';
                this.showReject = true;
                document.querySelector('[x-show="showReject"]').classList.remove('hidden');
            },
            // 驳回帖子
            async submitReject() {
                if (!this.rejectReason) {
                    alert('请选择拒绝理由')
                    return
                }

                if (this.loading) return;

                this.loading = true;
                try {
                    // API路径已正确使用/api/posts/{id}/audit
                    const response = await fetch(`/api/posts/${this.rejectTarget.id}/audit`, {
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
                        alert(`帖子 ${this.rejectTarget.id} 已拒绝，理由：${this.rejectReason}`);

                        const index = this.posts.findIndex(function(c) {
                            return c.id === this.rejectTarget.id;
                        }, this);
                        if (index !== -1) {
                            this.posts[index].audit_status = -2;
                            this.posts[index].reject_reason = this.rejectReason;
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
                    this.loading = false;
                }
            },

            openDelete(post) {
                this.deleteTarget = post;
                this.showDelete = true;
                document.querySelector('[x-show="showDelete"]').classList.remove('hidden');
            },

            async submitDelete() {
                if (this.loading || !this.deleteTarget) return;

                this.loading = true;
                try {
                    const response = await fetch(`/api/posts/${this.deleteTarget.id}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    });

                    const data = await response.json();
                    if (data.code === 200) {
                        alert(`帖子 ${this.deleteTarget.id} 已成功删除`);
                        // 更新本地数据
                        const index = this.posts.findIndex(function(c) {
                            return c.id === this.deleteTarget.id;
                        }, this);
                        if (index !== -1) {
                            this.posts[index].status = -1;
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
                    this.loading = false;
                }
            },
            // 恢复帖子
            async undoDelete(post) {
                if (this.loading) return;

                this.loading = true;
                try {
                    const response = await fetch(`/api/posts/${post.id}/restore`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    });

                    const data = await response.json();
                    if (data.code === 200) {
                        alert(`帖子 ${post.id} 已成功恢复`);
                        // 更新本地数据
                        const index = this.posts.findIndex(function(c) {
                            return c.id === this.deleteTarget.id;
                        }, this);
                        if (index !== -1) {
                            this.posts[index].status = 1;
                        }
                        this.showDelete = false;
                        this.deleteTarget = null;
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

            prevPage() {
                if (this.page > 1 && !this.loading) {
                    this.page--;
                    this.loading = true;
                    this.fetchPosts()
                }
            },
            // 切换上一页下一页
            nextPage() {
                if (this.hasMore && !this.loading) {
                    this.page++;
                    this.loading = true;
                    this.fetchPosts()
                }
            },
            init() {
                // 初始化时，将筛选条件复制到活跃筛选条件
                this.activeFilters = Object.assign({}, this.filters);
                this.fetchPosts()
            }
        }))
    })
</script>

<style>
    .picture-list {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .picture {
        width: 80px;
        height: 80px;
        overflow: hidden;
        border-radius: 4px;
        cursor: pointer;
    }

    .picture img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
</style>

@endsection