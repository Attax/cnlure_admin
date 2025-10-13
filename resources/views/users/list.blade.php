@extends('common.layouts')

@section('title', '用户管理')


@section('sidebar')
@parent
@endsection
@section('content')

<!-- 内容区 -->
<main x-data="userPage">
    <!-- 筛选搜索 -->
    <div class="flex justify-between items-center gap-4 bg-white p-4 mb-4">
        <h1 class="text-2xl font-bold">用户管理</h1>
        <div class="flex space-x-2">
            <input type="text" placeholder="搜索用户ID/昵称/手机号"
                class="border rounded px-3 py-2 w-60" x-model="filters.keyword">
            <select class="border rounded px-3 py-2" x-model="filters.status">
                <option value="">全部状态</option>
                <option value="1">正常</option>
                <option value="0">禁用</option>
                <option value="2">待审核</option>
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
                        <th class="px-4 py-2 min-w-[120px]">用户头像</th>
                        <th class="px-4 py-2 min-w-[150px]">昵称</th>
                        <th class="px-4 py-2 min-w-[150px]">手机号</th>
                        <th class="px-4 py-2 min-w-[150px]">注册时间</th>
                        <th class="px-4 py-2 min-w-[100px]">用户状态</th>
                        <th class="px-4 py-2 min-w-[120px]">账号类型</th>
                        <th class="px-4 py-2 min-w-[150px]">认证状态</th>
                        <th class="px-4 py-2 min-w-[120px]">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="user in users" :key="user.id">
                        <tr class="border-t">
                            <td class="px-4 py-2" x-text="user.id"></td>
                            <td class="px-4 py-2">
                                <img x-bind:src="user.avatar || '/assets/images/avatar.png'" alt="用户头像" class="w-10 h-10 rounded-full" />
                            </td>
                            <td class="px-4 py-2" x-text="user.nickname"></td>
                            <td class="px-4 py-2" x-text="user.phone || '未设置'"></td>
                            <td class="px-4 py-2" x-text="user.created_at"></td>
                            <td class="px-4 py-2">
                                <template x-if="user.status === 1">
                                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded font-medium">正常</span>
                                </template>
                                <template x-if="user.status === 0">
                                    <span class="px-3 py-1 bg-red-100 text-red-800 rounded font-medium">禁用</span>
                                </template>
                                <template x-if="user.status === 2">
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded font-medium">待审核</span>
                                </template>
                            </td>
                            <td class="px-4 py-2">
                                <template x-if="user.account_type === 1">
                                    <span class="px-3 py-1 bg-orange-100 text-orange-800 rounded font-medium">个人账号</span>
                                </template>
                                <template x-else-if="user.account_type === 2">
                                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded font-medium">组织账号</span>
                                </template>
                                <template x-else-if="user.account_type === 3">
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded font-medium">企业账号</span>
                                </template>
                                <template x-else-if="user.account_type === 99">
                                    <span class="px-3 py-1 bg-red-100 text-red-800 rounded font-medium">官方账号</span>
                                </template>
                                <template x-else>
                                    <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded font-medium">其他类型</span>
                                </template>
                            </td>
                            <td class="px-4 py-2">
                                <template x-if="user.verify_status === 1">
                                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded font-medium">已认证</span>
                                </template>
                                <template x-if="user.verify_status === 0">
                                    <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded font-medium">未认证</span>
                                </template>
                                <template x-if="user.verify_status === 2">
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded font-medium">审核中</span>
                                </template>
                            </td>
                            <td class="px-4 py-2 text-right">
                                <div class="flex flex-wrap justify-end gap-2">
                                    <button class="px-3 py-1 bg-blue-500 text-white rounded font-medium" @click="viewDetail(user)">详情</button>
                                    <button class="px-3 py-1 bg-yellow-500 text-white rounded font-medium" @click="editUser(user)">编辑</button>

                                    <template x-if="user.status === 1">
                                        <button class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 transition-colors" @click="openBanUser(user)">禁用</button>
                                    </template>
                                    <template x-if="user.status === 0">
                                        <button class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600 transition-colors" @click="openUnbanUser(user)">启用</button>
                                    </template>
                                </div>
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
        <div class="bg-white p-6 rounded-lg shadow-xl w-[800px] space-y-4">
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-lg">用户详情</h2>
                <button @click="showDetail=false" class="text-gray-500 hover:text-gray-700">×</button>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <div class="flex items-center">
                        <img x-bind:src="detailUser.avatar || '/assets/images/avatar.png'" alt="用户头像" class="w-20 h-20 rounded-full mr-4" />
                        <div>
                            <h3 class="text-xl font-semibold" x-text="detailUser.nickname"></h3>
                            <p class="text-gray-500" x-text="`ID: ${detailUser.id}`"></p>
                        </div>
                    </div>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500 mb-1">手机号</h4>
                    <p x-text="detailUser.phone || '未设置'"></p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500 mb-1">邮箱</h4>
                    <p x-text="detailUser.email || '未设置'"></p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500 mb-1">注册时间</h4>
                    <p x-text="detailUser.created_at"></p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500 mb-1">最后登录时间</h4>
                    <p x-text="detailUser.last_login_at || '从未登录'"></p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500 mb-1">用户状态</h4>
                    <p>
                        <template x-if="detailUser.status === 1">
                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded font-medium">正常</span>
                        </template>
                        <template x-if="detailUser.status === 0">
                            <span class="px-3 py-1 bg-red-100 text-red-800 rounded font-medium">禁用</span>
                        </template>
                        <template x-if="detailUser.status === 2">
                            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded font-medium">待审核</span>
                        </template>
                    </p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500 mb-1">账号类型</h4>
                    <p>
                          <template x-if="detailUser.account_type === 1">
                             <span class="px-3 py-1 bg-orange-100 text-orange-800 rounded font-medium">个人账号</span>
                          </template>
                          <template x-else-if="detailUser.account_type === 2">
                             <span class="px-3 py-1 bg-green-100 text-green-800 rounded font-medium">组织账号</span>
                          </template>
                          <template x-else-if="detailUser.account_type === 3">
                             <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded font-medium">企业账号</span>
                          </template>
                          <template x-else-if="detailUser.account_type === 99">
                             <span class="px-3 py-1 bg-red-100 text-red-800 rounded font-medium">官方账号</span>
                          </template>
                          <template x-else>
                             <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded font-medium">其他类型</span>
                          </template>
                      </p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500 mb-1">认证状态</h4>
                    <p>
                        <template x-if="detailUser.verify_status === 1">
                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded font-medium">已认证</span>
                        </template>
                        <template x-if="detailUser.verify_status === 0">
                            <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded font-medium">未认证</span>
                        </template>
                        <template x-if="detailUser.verify_status === 2">
                            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded font-medium">审核中</span>
                        </template>
                    </p>
                </div>
                <div class="col-span-2">
                    <h4 class="text-sm font-medium text-gray-500 mb-1">个人简介</h4>
                    <p x-text="detailUser.bio || '未设置'"></p>
                </div>
            </div>
            <div class="text-right">
                <button @click="showDetail=false" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 transition-colors">关闭</button>
            </div>
        </div>
    </div>

    <!-- 编辑用户弹窗 -->
    <div x-show="showEdit" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden" x-transition>
        <div class="bg-white p-6 rounded-lg shadow-xl w-[600px] space-y-4">
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-lg">编辑用户信息</h2>
                <button @click="showEdit=false" class="text-gray-500 hover:text-gray-700">×</button>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">昵称</label>
                    <input type="text" x-model="editUserForm.nickname" class="border rounded w-full px-3 py-2" placeholder="请输入昵称">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">手机号</label>
                    <input type="text" x-model="editUserForm.phone" class="border rounded w-full px-3 py-2" placeholder="请输入手机号">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">邮箱</label>
                    <input type="text" x-model="editUserForm.email" class="border rounded w-full px-3 py-2" placeholder="请输入邮箱">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">用户状态</label>
                    <select x-model="editUserForm.status" class="border rounded w-full px-3 py-2">
                        <option value="1">正常</option>
                        <option value="0">禁用</option>
                        <option value="2">待审核</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">账号类型</label>
                    <select x-model="editUserForm.account_type" class="border rounded w-full px-3 py-2">
                        <option value="0">其他类型</option>
                        <option value="1">个人账号</option>
                        <option value="2">组织账号</option>
                        <option value="3">企业账号</option>
                        <option value="99">官方账号</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">个人简介</label>
                    <textarea x-model="editUserForm.bio" class="border rounded w-full px-3 py-2" placeholder="请输入个人简介"></textarea>
                </div>
            </div>
            <div class="text-right space-x-2">
                <button @click="showEdit=false" class="px-4 py-2 border rounded hover:bg-gray-100 transition-colors">取消</button>
                <button @click="submitEditUser" :disabled="saving" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors flex items-center gap-2 justify-center">
                    <span x-show="!saving">保存</span>
                    <span x-show="saving" class="animate-spin">⟳</span>
                </button>
            </div>
        </div>
    </div>

    <!-- 禁用用户弹窗 -->
    <div x-show="showBanUser" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden" x-transition>
        <div class="bg-white p-6 rounded-lg shadow-xl w-[400px] space-y-4">
            <h2 class="font-semibold text-lg">确认禁用用户</h2>
            <p class="text-gray-700">确定要禁用用户 <span x-text="banTarget?.nickname"></span> 吗？禁用后该用户将无法登录系统。</p>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">禁用原因</label>
                <textarea x-model="banReason" class="border rounded w-full px-3 py-2" placeholder="请输入禁用原因"></textarea>
            </div>
            <div class="text-right space-x-2">
                <button @click="showBanUser=false" class="px-4 py-2 bg-gray-200 rounded">取消</button>
                <button @click="submitBanUser" class="px-4 py-2 bg-red-600 text-white rounded">确认禁用</button>
            </div>
        </div>
    </div>

    <!-- 启用用户弹窗 -->
    <div x-show="showUnbanUser" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden" x-transition>
        <div class="bg-white p-6 rounded-lg shadow-xl w-[400px] space-y-4">
            <h2 class="font-semibold text-lg">确认启用用户</h2>
            <p class="text-gray-700">确定要启用用户 <span x-text="unbanTarget?.nickname"></span> 吗？启用后该用户将可以正常登录系统。</p>
            <div class="text-right space-x-2">
                <button @click="showUnbanUser=false" class="px-4 py-2 bg-gray-200 rounded">取消</button>
                <button @click="submitUnbanUser" class="px-4 py-2 bg-green-600 text-white rounded">确认启用</button>
            </div>
        </div>
    </div>


</main>
@endsection

@section('scripts')
@parent
<script>
    document.addEventListener('alpine:init', function() {
        Alpine.data('userPage', function() {
            return {
                users: [],
                page: 1,
                pageSize: 10,
                loading: false,
                hasMore: true,
                filters: {
                    keyword: '',
                    status: ''
                },
                activeFilters: {},
                showDetail: false,
                detailUser: {},
                showEdit: false,
                editUserForm: {},
                saving: false,
                showBanUser: false,
                banTarget: null,
                banReason: '',
                showUnbanUser: false,
                unbanTarget: null,


                init() {
                    // 初始化时复制筛选条件并加载数据
                    this.activeFilters = {
                        ...this.filters
                    };
                    this.fetchUsers();
                },

                async fetchUsers() {
                    this.loading = true;
                    try {
                        // 调用后端API获取用户列表
                        const params = new URLSearchParams({
                            page: this.page,
                            page_size: this.pageSize,
                            keyword: encodeURIComponent(this.activeFilters.keyword),
                            status: this.activeFilters.status
                        });


                        const {
                            code,
                            data,
                            pagination
                        } = await fetch(`/api/users?${params}`).then(res => res.json());
                        this.users = data || [];
                        this.hasMore = pagination.has_more || false;


                    } catch (error) {
                        console.error('获取用户列表失败:', error);
                        alert('获取用户列表失败');
                        // 使用模拟数据作为备用
                        const mockUsers = [];
                        for (let i = 1; i <= 5; i++) {
                            mockUsers.push({
                                id: i,
                                nickname: `用户${i}`,
                                avatar: `/assets/images/avatar.png`,
                                phone: null,
                                email: null,
                                created_at: `2023-10-0${i} 12:30:45`,
                                last_login_at: null,
                                status: 1,
                                verify_status: 0,
                            account_type: i % 4 === 0 ? 1 : (i % 4 === 1 ? 2 : (i % 4 === 2 ? 3 : 0)),
                            bio: null
                            });
                        }
                        this.users = mockUsers;
                        this.hasMore = false;
                    } finally {
                        this.loading = false;
                    }
                },

                search() {
                    this.activeFilters = {
                        ...this.filters
                    };
                    this.page = 1;
                    this.fetchUsers();
                },

                prevPage() {
                    if (this.page > 1) {
                        this.page--;
                        this.fetchUsers();
                    }
                },

                nextPage() {
                    if (this.hasMore) {
                        this.page++;
                        this.fetchUsers();
                    }
                },

                async viewDetail(user) {
                    try {
                        // 调用后端API获取用户详情
                        const response = await fetch(`/users/detail/${user.id}`);
                        const data = await response.json();

                        if (data.code === 0) {
                            this.detailUser = data.data;
                        } else {
                            // 如果API调用失败，使用本地数据
                            console.warn('API调用失败，使用本地数据');
                            this.detailUser = {
                                ...user
                            };
                        }
                        this.showDetail = true;
                        document.querySelector('[x-show="showDetail"]').classList.remove('hidden');
                    } catch (error) {
                        console.error('获取用户详情失败:', error);
                        // 使用本地数据作为备用
                        this.detailUser = {
                            ...user
                        };
                        this.showDetail = true;
                        document.querySelector('[x-show="showDetail"]').classList.remove('hidden');
                    }
                },

                async editUser(user) {
                    try {
                        // 调用后端API获取用户详情
                        const response = await fetch(`/users/detail/${user.id}`);
                        const data = await response.json();

                        if (data.code === 0) {
                            const userData = data.data;
                            this.editUserForm = {
                                id: userData.id,
                                nickname: userData.nickname,
                                phone: userData.phone || '',
                                email: userData.email || '',
                                status: userData.status,
                                account_type: userData.account_type || 0,
                                bio: userData.bio || ''
                            };
                        } else {
                            // 如果API调用失败，使用本地数据
                            console.warn('API调用失败，使用本地数据');
                            this.editUserForm = {
                                id: user.id,
                                nickname: user.nickname,
                                phone: user.phone || '',
                                email: user.email || '',
                                status: user.status,
                                account_type: user.account_type || 0,
                                bio: user.bio || ''
                            };
                        }
                        this.showEdit = true;
                        document.querySelector('[x-show="showEdit"]').classList.remove('hidden');
                    } catch (error) {
                        console.error('获取用户详情失败:', error);
                        // 使用本地数据作为备用
                        this.editUserForm = {
                            id: user.id,
                            nickname: user.nickname,
                            phone: user.phone || '',
                            email: user.email || '',
                            status: user.status,
                            bio: user.bio || ''
                        };
                        this.showEdit = true;
                        document.querySelector('[x-show="showEdit"]').classList.remove('hidden');
                    }
                },

                async submitEditUser() {
                    this.saving = true;
                    try {
                        // 调用后端API保存用户信息
                        const response = await fetch(`/users/edit/${this.editUserForm.id}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                            },
                            body: JSON.stringify(this.editUserForm)
                        });
                        const data = await response.json();

                        if (data.code === 0) {
                            // 更新本地数据
                            const index = this.users.findIndex(u => u.id === this.editUserForm.id);
                            if (index !== -1) {
                                this.users[index] = {
                                    ...this.users[index],
                                    nickname: this.editUserForm.nickname,
                                    phone: this.editUserForm.phone,
                                    email: this.editUserForm.email,
                                    status: this.editUserForm.status,
                                    bio: this.editUserForm.bio
                                };
                            }
                            this.showEdit = false;
                            alert('用户信息更新成功');
                        } else {
                            alert('更新失败: ' + (data.message || '未知错误'));
                        }
                    } catch (error) {
                        console.error('更新用户信息失败:', error);
                        alert('更新用户信息失败');
                    } finally {
                        this.saving = false;
                    }
                },

                openBanUser(user) {
                    this.banTarget = {
                        ...user
                    };
                    this.banReason = '';
                    this.showBanUser = true;
                    document.querySelector('[x-show="showBanUser"]').classList.remove('hidden');
                },

                async submitBanUser() {
                    if (!this.banReason.trim()) {
                        alert('请输入禁用原因');
                        return;
                    }

                    try {
                        // 调用后端API禁用用户
                        const response = await fetch(`/users/ban/${this.banTarget.id}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                            },
                            body: JSON.stringify({
                                reason: this.banReason
                            })
                        });
                        const data = await response.json();

                        if (data.code === 0) {
                            // 更新本地数据
                            const index = this.users.findIndex(u => u.id === this.banTarget.id);
                            if (index !== -1) {
                                this.users[index].status = 0;
                            }
                            this.showBanUser = false;
                            alert('用户禁用成功');
                        } else {
                            alert('禁用失败: ' + (data.message || '未知错误'));
                        }
                    } catch (error) {
                        console.error('禁用用户失败:', error);
                        alert('禁用用户失败');
                    }
                },

                openUnbanUser(user) {
                    this.unbanTarget = {
                        ...user
                    };
                    this.showUnbanUser = true;
                    document.querySelector('[x-show="showUnbanUser"]').classList.remove('hidden');
                },

                async submitUnbanUser() {
                    try {
                        // 调用后端API启用用户
                        const response = await fetch(`/users/unban/${this.unbanTarget.id}`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                            }
                        });
                        const data = await response.json();

                        if (data.code === 0) {
                            // 更新本地数据
                            const index = this.users.findIndex(u => u.id === this.unbanTarget.id);
                            if (index !== -1) {
                                this.users[index].status = 1;
                            }
                            this.showUnbanUser = false;
                            alert('用户启用成功');
                        } else {
                            alert('启用失败: ' + (data.message || '未知错误'));
                        }
                    } catch (error) {
                        console.error('启用用户失败:', error);
                        alert('启用用户失败');
                    }
                },


            };
        });
    });
</script>
@endsection