@extends('common.layouts')

@section('title', '钓场管理')


@section('sidebar')
@parent
@endsection
@section('content')

<!-- 内容区 -->
<main x-data="fishingSpotsManager" class="flex-1">
    <!-- 筛选搜索 -->
    <div class="flex justify-between items-center gap-4 bg-white p-4 mb-4">
        <h1 class="text-2xl font-bold">钓场管理</h1>
        <div class="flex space-x-2">
            <button @click="openAddModal()" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 flex items-center justify-center gap-2">
                添加钓场
            </button>
            <input type="text" placeholder="搜索钓场名称/地址"
                class="border rounded px-3 py-2 w-60" x-model="filters.keyword">
            <select class="border rounded px-3 py-2" x-model="filters.status">
                <option value="">全部状态</option>
                <option value="1">启用</option>
                <option value="0">禁用</option>
            </select>
            <button @click="search()" :disabled="loading" class="px-4 py-2 bg-black text-white rounded hover:bg-gray-800 flex items-center justify-center gap-2">
                <span x-show="!loading">搜索</span>
                <span x-show="loading" class="animate-spin">⟳</span>
            </button>
        </div>
    </div>

    <!-- 数据表格 -->
    <div class="bg-white shadow-sm w-full">
        <div class="overflow-x-auto w-full">
            <table class="w-full text-sm min-w-[1200px]">
                <thead class="bg-white">
                    <tr>
                        <th class="px-4 py-2 min-w-[80px]">ID</th>
                        <th class="px-4 py-2 min-w-[200px]">钓场名称</th>
                        <th class="px-4 py-2 min-w-[250px]">地址</th>
                        <th class="px-4 py-2 min-w-[120px]">联系电话</th>
                        <th class="px-4 py-2 min-w-[120px]">联系人</th>
                        <th class="px-4 py-2 min-w-[150px]">钓场主</th>
                        <th class="px-4 py-2 min-w-[100px]">状态</th>
                          <th class="px-4 py-2 min-w-[150px]">创建时间</th>
                        <th class="px-4 py-2 min-w-[150px]">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="spot in spots" :key="spot.id">
                        <tr class="border-t">
                            <td class="px-4 py-2" x-text="spot.id"></td>
                            <td class="px-4 py-2">
                                <div class="flex items-center">
                                    <template x-if="spot.image_urls && spot.image_urls.length > 0">
                                        <img :src="spot.image_urls[0]" alt="钓场图片" class="w-12 h-12 rounded mr-3 object-cover">
                                    </template>
                                    <span x-text="spot.name"></span>
                                </div>
                            </td>
                            <td class="px-4 py-2 truncate max-w-xs" x-text="spot.address"></td>
                            <td class="px-4 py-2" x-text="spot.contact_phone"></td>
                            <td class="px-4 py-2" x-text="spot.contact_name"></td>
                            <td class="px-4 py-2">
                                <template x-if="spot.user">
                                    <span class="text-blue-600" x-text="spot.user.name"></span>
                                </template>
                                <template x-if="!spot.user">
                                    <span class="text-gray-500">未关联</span>
                                </template>
                            </td>
                            <td class="px-4 py-2">
                                  <template x-if="spot.status === 1">
                                      <span class="px-3 py-1 bg-green-100 text-green-800 rounded font-medium">启用</span>
                                  </template>
                                  <template x-if="spot.status === 0">
                                      <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded font-medium">禁用</span>
                                  </template>
                              </td>
                            <td class="px-4 py-2" x-text="spot.created_at"></td>
                            <td class="px-4 py-2">
                                <div class="flex flex-wrap gap-2">
                                    <button class="px-3 py-1 bg-blue-500 text-white rounded font-medium" @click="viewDetail(spot)">查看</button>
                                    <button class="px-3 py-1 bg-yellow-500 text-white rounded font-medium" @click="editSpot(spot)">编辑</button>
                                    <button class="px-3 py-1 bg-red-500 text-white rounded font-medium" @click="openDelete(spot)">删除</button>
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
        <button @click="prevPage" :disabled="page<=1" class="px-3 py-1 border rounded" x-show="page>1">上一页</button>
        <button @click="nextPage" :disabled="!hasMore" class="px-3 py-1 border rounded" x-show="hasMore">下一页</button>
    </div>

    <!-- 查看详情弹窗 -->
    <div x-show="showDetail" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden" x-transition>
        <div class="bg-white p-6 rounded-lg shadow-xl w-[700px] max-h-[80vh] overflow-y-auto">
            <h2 class="font-semibold text-lg mb-4">钓场详情</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="font-medium mb-2">基本信息</h3>
                    <p><span class="text-gray-600">ID：</span><span x-text="currentSpot.id"></span></p>
                    <p><span class="text-gray-600">名称：</span><span x-text="currentSpot.name"></span></p>
                    <p><span class="text-gray-600">地址：</span><span x-text="currentSpot.address"></span></p>
                    <p><span class="text-gray-600">经纬度：</span><span x-text="currentSpot.latitude + ', ' + currentSpot.longitude"></span></p>
                    <p><span class="text-gray-600">联系人：</span><span x-text="currentSpot.contact_name"></span></p>
                    <p><span class="text-gray-600">联系电话：</span><span x-text="currentSpot.contact_phone"></span></p>
                    <p><span class="text-gray-600">钓场面积：</span><span x-text="currentSpot.area || '-'"></span> 平方米</p>
                    <p><span class="text-gray-600">水域类型：</span><span x-text="currentSpot.water_type === 0 ? '天然' : currentSpot.water_type === 1 ? '人工' : '-'" class="px-2 py-1 rounded-full text-sm font-medium"></span></p>
                    <p><span class="text-gray-600">水质类型：</span><span x-text="currentSpot.water_quality === 0 ? '淡水' : currentSpot.water_quality === 1 ? '海水' : currentSpot.water_quality === 2 ? '咸淡水' : '-'" class="px-2 py-1 rounded-full text-sm font-medium"></span></p>
                    <p><span class="text-gray-600">状态：</span><span x-text="currentSpot.status === 1 ? '启用' : '禁用'"></span></p>
                    <p><span class="text-gray-600">营业状态：</span>
                        <template x-if="currentSpot.business_status === 1">
                            <span class="text-blue-600">营业中</span>
                        </template>
                        <template x-if="currentSpot.business_status === 0">
                            <span class="text-yellow-600">休息中</span>
                        </template>
                    </p>
                    <p><span class="text-gray-600">钓场主：</span>
                        <template x-if="currentSpot.user">
                            <span class="text-blue-600" x-text="currentSpot.user.name"></span>
                        </template>
                        <template x-if="!currentSpot.user">
                            <span class="text-gray-500">未关联</span>
                        </template>
                    </p>
                    <p><span class="text-gray-600">创建时间：</span><span x-text="currentSpot.created_at"></span></p>
                </div>
                <div>
                    <h3 class="font-medium mb-2">其他信息</h3>
                    <p><span class="text-gray-600">营业时间：</span><span x-text="currentSpot.business_hours"></span></p>
                    <p><span class="text-gray-600">价格说明：</span><span x-text="currentSpot.price_description"></span></p>
                    <p><span class="text-gray-600">设施：</span>
                    <div class="inline-block mt-1">
                        <template x-for="facility in currentSpot.facilities" :key="facility">
                            <span class="px-2 py-1 bg-gray-100 rounded mr-1 mb-1">
                                <span x-text="facility"></span>
                            </span>
                        </template>
                    </div>
                    </p>
                    <p><span class="text-gray-600">鱼类品种：</span>
                    <div class="inline-block mt-1">
                        <template x-for="fish in currentSpot.fish_species" :key="fish">
                            <span class="px-2 py-1 bg-gray-100 rounded mr-1 mb-1">
                                <span x-text="fish"></span>
                            </span>
                        </template>
                    </div>
                    </p>
                </div>
            </div>
            <div class="mt-6">
                <h3 class="font-medium mb-2">图片展示</h3>
                <div class="grid grid-cols-3 gap-3">
                    <template x-for="(image, index) in currentSpot.image_urls" :key="index">
                        <div class="aspect-square overflow-hidden rounded cursor-pointer" @click="showImagePreview(image, index)">
                            <img :src="image" alt="钓场图片" class="w-full h-full object-cover">
                        </div>
                    </template>
                </div>
            </div>
            <div class="text-right mt-6">
                <button @click="showDetail=false" class="px-4 py-2 bg-gray-200 rounded">关闭</button>
            </div>
        </div>
    </div>

    <!-- 添加/编辑钓场弹窗 -->
    <div x-show="showEditModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden" x-transition>
        <div class="bg-white p-6 rounded-lg shadow-xl w-[800px] max-h-[80vh] overflow-y-auto">
            <h2 class="font-semibold text-lg mb-4" x-text="isEdit ? '编辑钓场' : '添加钓场'"></h2>
            <form @submit.prevent="saveSpot">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">钓场名称 <span class="text-red-500">*</span></label>
                        <input type="text" x-model="formData.name" required class="border rounded w-full px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">联系人</label>
                        <input type="text" x-model="formData.contact_name" class="border rounded w-full px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">联系电话</label>
                        <input type="text" x-model="formData.contact_phone" class="border rounded w-full px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">状态</label>
                        <select x-model="formData.status" class="border rounded w-full px-3 py-2">
                            <option value="1">启用</option>
                            <option value="0">禁用</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">营业状态</label>
                        <select x-model="formData.business_status" class="border rounded w-full px-3 py-2">
                            <option value="1">营业中</option>
                            <option value="0">休息中</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">关联企业用户</label>
                        <select x-model="formData.user_id" class="border rounded w-full px-3 py-2">
                            <option value="">不关联</option>
                            <!-- 这里将通过后端API获取企业用户列表 -->
                            <template x-for="user in enterpriseUsers" :key="user.id">
                                <option :value="user.id" x-text="user.name"></option>
                            </template>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">地址 <span class="text-red-500">*</span></label>
                        <input type="text" x-model="formData.address" required class="border rounded w-full px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">钓场面积（平方米）</label>
                        <input type="number" min="0" step="0.01" x-model="formData.area" class="border rounded w-full px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">水域类型</label>
                        <select x-model="formData.water_type" class="border rounded w-full px-3 py-2">
                            <option value="">请选择</option>
                            <option value="0">天然</option>
                            <option value="1">人工</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">水质类型</label>
                        <select x-model="formData.water_quality" class="border rounded w-full px-3 py-2">
                            <option value="">请选择</option>
                            <option value="0">淡水</option>
                            <option value="1">海水</option>
                            <option value="2">咸淡水</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">纬度</label>
                        <input type="number" step="0.000001" x-model="formData.latitude" class="border rounded w-full px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">经度</label>
                        <input type="number" step="0.000001" x-model="formData.longitude" class="border rounded w-full px-3 py-2">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">营业时间</label>
                        <input type="text" x-model="formData.business_hours" class="border rounded w-full px-3 py-2">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">价格说明</label>
                        <input type="text" x-model="formData.price_description" class="border rounded w-full px-3 py-2">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">设施（逗号分隔）</label>
                        <input type="text" x-model="formData.facilities_str" class="border rounded w-full px-3 py-2" placeholder="如：停车场,餐厅,卫生间">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">鱼类品种（逗号分隔）</label>
                        <input type="text" x-model="formData.fish_species_str" class="border rounded w-full px-3 py-2" placeholder="如：草鱼,鲤鱼,鲫鱼">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">钓场图片</label>
                        <div class="mt-2">
                            <!-- 图片上传按钮 -->
                            <button type="button" @click="triggerFileUpload" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 mb-4">
                                上传图片
                            </button>
                            <input type="file" id="imageUpload" @change="handleImageUpload" accept="image/*" multiple class="hidden">
                            
                            <!-- 图片预览区域 -->
                            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-2 mt-2" x-show="formData.image_urls && formData.image_urls.length > 0">
                                <template x-for="(url, index) in formData.image_urls" :key="index">
                                    <div class="relative group">
                                        <img :src="url" alt="钓场图片" class="w-full aspect-square object-cover rounded">
                                        <button 
                                            type="button" 
                                            @click="removeImage(index)"
                                            class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
                                        >
                                            ×
                                        </button>
                                    </div>
                                </template>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">最多上传5张图片，支持JPG、PNG格式</p>
                        </div>
                    </div>
                </div>
                <div class="text-right mt-6 space-x-2">
                    <button type="button" @click="showEditModal=false" class="px-4 py-2 bg-gray-200 rounded">取消</button>
                    <button type="submit" :disabled="loading" class="px-4 py-2 bg-blue-600 text-white rounded">保存</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 删除确认弹窗 -->
    <div x-show="showDelete" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden" x-transition>
        <div class="bg-white p-6 rounded-lg shadow-xl w-[400px]">
            <h2 class="font-semibold text-lg mb-4">确认删除</h2>
            <p class="text-gray-700">确定要删除ID为 <span x-text="deleteTarget?.id"></span> 的钓场吗？此操作不可恢复。</p>
            <div class="text-right mt-6 space-x-2">
                <button @click="showDelete=false" class="px-4 py-2 bg-gray-200 rounded">取消</button>
                <button @click="submitDelete" :disabled="loading" class="px-4 py-2 bg-red-600 text-white rounded">确认删除</button>
            </div>
        </div>
    </div>

    <!-- 图片预览弹窗 -->
    <div x-show="showImageModal" class="fixed inset-0 bg-black/70 flex items-center justify-center z-50 overflow-auto hidden" x-transition
        @click="showImageModal=false"
        @keydown.escape="showImageModal=false">
        <div class="bg-white rounded-lg shadow-xl w-[90vw] max-w-[70vw] p-6" @click.stop>
            <div class="mb-4 flex justify-between items-center">
                <div class="bg-black/60 text-white px-4 py-2 rounded-full text-sm">
                    <span x-text="currentImageIndex + 1"></span> / <span x-text="allImageUrls.length"></span>
                </div>
                <button @click="showImageModal=false" class="text-black text-2xl hover:text-gray-600 transition-colors">×</button>
            </div>
            <div class="flex justify-center items-center py-4">
                <img :src="previewImageUrl" alt="预览图片" class="max-w-full max-h-[70vh] h-auto object-contain">
            </div>
            <div class="flex justify-between mt-2">
                <button @click="prevImage" :disabled="currentImageIndex === 0"
                    class="bg-black/60 text-white px-6 py-3 rounded-full hover:bg-black/80 transition-colors"
                    :class="{ 'opacity-50 cursor-not-allowed': currentImageIndex === 0 }">
                    上一张
                </button>
                <button @click="nextImage" :disabled="currentImageIndex >= allImageUrls.length - 1"
                    class="bg-black/60 text-white px-6 py-3 rounded-full hover:bg-black/80 transition-colors"
                    :class="{ 'opacity-50 cursor-not-allowed': currentImageIndex >= allImageUrls.length - 1 }">
                    下一张
                </button>
            </div>
        </div>
    </div>
</main>
@endsection

@section('scripts')
@parent
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('fishingSpotsManager', () => ({
            // 数据状态
            spots: [],
            currentSpot: {
                id: '',
                name: '',
                address: '',
                latitude: '',
                longitude: '',
                contact_name: '',
                contact_phone: '',
                status: 0,
                business_status: 0,
                user: null,
                created_at: '',
                business_hours: '',
                price_description: '',
                facilities: [],
                fish_species: [],
                image_urls: [],
                area: '',
                water_type: '',
                water_quality: ''
            },
            formData: {
                name: '',
                address: '',
                latitude: '',
                longitude: '',
                contact_name: '',
                contact_phone: '',
                business_hours: '',
                price_description: '',
                facilities_str: '',
                fish_species_str: '',
                status: 1,
                business_status: 1,
                user_id: '',
                image_urls: [],
                area: '',
                water_type: '',
                water_quality: ''
            },

            // 筛选相关
            filters: {
                keyword: '',
                status: ''
            },
            activeFilters: {
                keyword: '',
                status: ''
            },

            // UI状态
            showDetail: false,
            showEditModal: false,
            showDelete: false,
            showImageModal: false,
            isEdit: false,
            loading: false,

            // 分页相关
            page: 1,
            pageSize: 10,
            hasMore: false,

            // 操作目标
            deleteTarget: null,

            // 图片预览
            currentImageIndex: 0,
            allImageUrls: [],
            previewImageUrl: '',

            // 企业用户数据
            enterpriseUsers: [{
                    id: '',
                    name: '未分配'
                },
                {
                    id: 1,
                    name: '杭州渔友公司'
                },
                {
                    id: 2,
                    name: '宁波海钓俱乐部'
                },
                {
                    id: 3,
                    name: '广州渔乐圈企业'
                }
            ],

            // 获取钓场列表
            async fetchSpots() {
                // 模拟企业用户数据
                this.enterpriseUsers = [{
                        id: 1,
                        name: '北京钓鱼协会'
                    },
                    {
                        id: 2,
                        name: '上海垂钓俱乐部'
                    },
                    {
                        id: 3,
                        name: '广州渔乐圈企业'
                    }
                ];

                // 模拟数据 - 直接在前端提供数据
                const mockSpots = [{
                        id: 1,
                        name: '青山水库',
                        address: '浙江省杭州市临安区太湖源镇',
                        latitude: 30.2456,
                        longitude: 119.7823,
                        contact_name: '张三',
                        contact_phone: '13800138001',
                        business_hours: '06:00-20:00',
                        price_description: '200元/天，提供鱼竿租赁',
                        facilities: ['停车场', '餐厅', '卫生间', '钓鱼指导'],
                        fish_species: ['草鱼', '鲤鱼', '鲫鱼', '鲈鱼'],
                        status: 1,
                        business_status: 1,
                        user_id: 1,
                        user: {
                            id: 1,
                            name: '北京钓鱼协会'
                        },
                        created_at: '2024-01-01 10:00:00',
                        image_urls: ['https://picsum.photos/id/10/400/300', 'https://picsum.photos/id/11/400/300'],
                        area: 15000,
                        water_type: 0,
                        water_quality: 0
                    },
                    {
                        id: 2,
                        name: '西湖垂钓中心',
                        address: '浙江省杭州市西湖区北山路',
                        latitude: 30.2414,
                        longitude: 120.1497,
                        contact_name: '李四',
                        contact_phone: '13900139002',
                        business_hours: '08:00-18:00',
                        price_description: '300元/天，含午餐',
                        facilities: ['停车场', '餐厅', '休息室'],
                        fish_species: ['草鱼', '鲤鱼', '鳙鱼', '鲢鱼'],
                        status: 1,
                        business_status: 0,
                        user_id: 2,
                        user: {
                            id: 2,
                            name: '上海垂钓俱乐部'
                        },
                        created_at: '2024-01-02 14:30:00',
                        image_urls: ['https://picsum.photos/id/13/400/300'],
                        area: 8000,
                        water_type: 0,
                        water_quality: 0
                    },
                    {
                        id: 3,
                        name: '千岛湖钓鱼基地',
                        address: '浙江省杭州市淳安县千岛湖镇',
                        latitude: 29.5417,
                        longitude: 119.0292,
                        contact_name: '王五',
                        contact_phone: '13700137003',
                        business_hours: '全天开放',
                        price_description: '500元/天，提供住宿',
                        facilities: ['停车场', '餐厅', '住宿', '钓鱼船'],
                        fish_species: ['鲈鱼', '翘嘴', '鳜鱼', '鲶鱼'],
                        status: 1,
                        business_status: 1,
                        user_id: null,
                        user: null,
                        created_at: '2024-01-03 09:15:00',
                        image_urls: ['https://picsum.photos/id/15/400/300', 'https://picsum.photos/id/16/400/300', 'https://picsum.photos/id/17/400/300'],
                        area: 50000,
                        water_type: 0,
                        water_quality: 0
                    },
                    {
                        id: 4,
                        name: '西溪湿地钓场',
                        address: '浙江省杭州市西湖区西溪湿地公园',
                        latitude: 30.2422,
                        longitude: 120.0947,
                        contact_name: '赵六',
                        contact_phone: '13600136004',
                        business_hours: '07:00-19:00',
                        price_description: '150元/天',
                        facilities: ['停车场', '卫生间'],
                        fish_species: ['鲫鱼', '鲤鱼', '草鱼'],
                        status: 0,
                        business_status: 1,
                        user_id: 3,
                        user: {
                            id: 3,
                            name: '广州渔乐圈企业'
                        },
                        created_at: '2024-01-04 16:45:00',
                        image_urls: ['https://picsum.photos/id/19/400/300'],
                        area: 12000,
                        water_type: 0,
                        water_quality: 0
                    },
                    {
                        id: 5,
                        name: '钱塘江钓鱼码头',
                        address: '浙江省杭州市滨江区钱塘江畔',
                        latitude: 30.2154,
                        longitude: 120.2168,
                        contact_name: '钱七',
                        contact_phone: '13500135005',
                        business_hours: '05:00-22:00',
                        price_description: '免费，需自带装备',
                        facilities: ['公共厕所'],
                        fish_species: ['鲤鱼', '草鱼', '鲈鱼', '翘嘴'],
                        status: 1,
                        business_status: 0,
                        user_id: null,
                        user: null,
                        created_at: '2024-01-05 11:20:00',
                        image_urls: ['https://picsum.photos/id/21/400/300', 'https://picsum.photos/id/22/400/300'],
                        area: 20000,
                        water_type: 0,
                        water_quality: 2
                    }
                ];

                // 模拟筛选
                let filteredSpots = [...mockSpots];

                if (this.activeFilters.keyword && this.activeFilters.keyword.trim() !== '') {
                    const keyword = this.activeFilters.keyword.toLowerCase();
                    filteredSpots = filteredSpots.filter(spot =>
                        spot.name.toLowerCase().includes(keyword) ||
                        spot.address.toLowerCase().includes(keyword)
                    );
                }

                if (this.activeFilters.status !== undefined && this.activeFilters.status !== null && this.activeFilters.status !== '') {
                    const status = parseInt(this.activeFilters.status);
                    filteredSpots = filteredSpots.filter(spot => spot.status === status);
                }

                // 模拟分页
                const start = (this.page - 1) * this.pageSize;
                const end = start + this.pageSize;
                const paginatedSpots = filteredSpots.slice(start, end);

                // 模拟网络延迟
                setTimeout(() => {
                    this.spots = paginatedSpots;
                    this.hasMore = end < filteredSpots.length;
                    this.loading = false;
                }, 300);
            },

            // 搜索
            search() {
                this.activeFilters = Object.assign({}, this.filters);
                this.page = 1;
                this.loading = true;
                this.fetchSpots();
            },

            // 查看详情
            async viewDetail(spot) {
                // 使用前端模拟数据，直接使用传入的spot对象
                // 模拟网络延迟
                setTimeout(() => {
                    // 复制spot对象，避免直接修改原数据
                    this.currentSpot = {
                        ...spot
                    };
                    this.showDetail = true;
                    // 确保弹窗可见
                    const detailModal = document.querySelector('[x-show="showDetail"]');
                    if (detailModal) {
                        detailModal.classList.remove('hidden');
                    }
                }, 200);
            },

            // 打开添加弹窗
            openAddModal() {
                this.isEdit = false;
                this.formData = {
                    name: '',
                    address: '',
                    latitude: '',
                    longitude: '',
                    contact_name: '',
                    contact_phone: '',
                    business_hours: '',
                    price_description: '',
                    facilities_str: '',
                    fish_species_str: '',
                    status: 1,
                    business_status: 1,
                    user_id: '',
                    image_urls: [],
                    area: '',
                    water_type: '',
                    water_quality: ''
                };
                this.showEditModal = true;
                document.querySelector('[x-show="showEditModal"]').classList.remove('hidden');
            },
            
            // 触发文件上传
            triggerFileUpload() {
                document.getElementById('imageUpload').click();
            },
            
            // 处理图片上传
            handleImageUpload(event) {
                const files = event.target.files;
                if (!files || files.length === 0) return;
                
                // 限制最多上传5张图片
                const maxImages = 5;
                const remainingSlots = maxImages - this.formData.image_urls.length;
                
                if (files.length > remainingSlots) {
                    alert(`最多只能上传${maxImages}张图片，还有${remainingSlots}个空位`);
                    return;
                }
                
                // 模拟上传，实际项目中这里应该发送文件到服务器
                // 这里使用picsum.photos作为占位图片URL
                for (let i = 0; i < files.length; i++) {
                    // 使用随机ID生成picsum图片URL
                    const randomId = Math.floor(Math.random() * 1000) + 1;
                    const imageUrl = `https://picsum.photos/id/${randomId}/400/300`;
                    this.formData.image_urls.push(imageUrl);
                }
                
                // 清空input，允许重复上传相同文件
                event.target.value = '';
            },
            
            // 删除图片
            removeImage(index) {
                this.formData.image_urls.splice(index, 1);
            },

            // 编辑钓场
            editSpot(spot) {
                this.isEdit = true;
                this.currentSpot = spot; // 保存当前编辑的钓场对象
                this.formData = {
                    name: spot.name,
                    address: spot.address,
                    latitude: spot.latitude,
                    longitude: spot.longitude,
                    contact_name: spot.contact_name || '',
                    contact_phone: spot.contact_phone || '',
                    business_hours: spot.business_hours || '',
                    price_description: spot.price_description || '',
                    facilities_str: (spot.facilities || []).join(','),
                    fish_species_str: (spot.fish_species || []).join(','),
                    status: spot.status,
                    business_status: spot.business_status || 1,
                    user_id: spot.user_id || '',
                    image_urls: [...(spot.image_urls || [])], // 复制现有图片URL数组
                    area: spot.area || '',
                    water_type: spot.water_type || '',
                    water_quality: spot.water_quality || ''
                };
                this.showEditModal = true;
                document.querySelector('[x-show="showEditModal"]').classList.remove('hidden');
            },

            // 保存钓场信息
            async saveSpot() {
                if (this.loading) return;
                this.loading = true;

                try {
                    const spotData = {
                        ...this.formData,
                        facilities: this.formData.facilities_str ? this.formData.facilities_str.split(',').map(item => item.trim()) : [],
                        fish_species: this.formData.fish_species_str ? this.formData.fish_species_str.split(',').map(item => item.trim()) : [],
                        created_at: new Date().toLocaleString('zh-CN', {
                            year: 'numeric',
                            month: '2-digit',
                            day: '2-digit',
                            hour: '2-digit',
                            minute: '2-digit',
                            second: '2-digit',
                            hour12: false
                        }).replace(/\//g, '-')
                    };

                    delete spotData.facilities_str;
                    delete spotData.fish_species_str;

                    // 模拟网络延迟
                    setTimeout(() => {
                        if (this.isEdit) {
                            // 编辑操作
                            const index = this.spots.findIndex(s => s.id === this.currentSpot.id);
                            if (index !== -1) {
                                this.spots[index] = {
                                    ...this.spots[index],
                                    ...spotData
                                };
                            }
                            alert('修改成功');
                        } else {
                            // 添加操作
                            const newSpot = {
                                ...spotData,
                                id: Date.now(), // 生成临时ID
                                image_urls: ['https://picsum.photos/id/25/400/300'] // 默认图片
                            };
                            this.spots.unshift(newSpot);
                            alert('添加成功');
                        }
                        this.showEditModal = false;
                        this.loading = false;
                    }, 500);
                } catch (e) {
                    console.error('保存钓场信息失败', e);
                    alert('保存失败，请稍后重试');
                    this.loading = false;
                }
            },

            // 打开删除弹窗
            openDelete(spot) {
                this.deleteTarget = spot;
                this.showDelete = true;
                document.querySelector('[x-show="showDelete"]').classList.remove('hidden');
            },

            // 确认删除
            async submitDelete() {
                if (this.loading || !this.deleteTarget) return;
                this.loading = true;

                // 模拟网络延迟
                setTimeout(() => {
                    try {
                        // 直接从前端数据中删除
                        this.spots = this.spots.filter(spot => spot.id !== this.deleteTarget.id);
                        this.showDelete = false;
                        this.deleteTarget = null;
                        alert('删除成功');
                    } catch (e) {
                        console.error('删除钓场失败', e);
                        alert('删除失败，请稍后重试');
                    } finally {
                        this.loading = false;
                    }
                }, 300);
            },

            // 图片预览
            showImagePreview(url, index) {
                this.currentImageIndex = index;
                this.allImageUrls = this.currentSpot.image_urls || [];
                this.previewImageUrl = url;
                this.showImageModal = true;
                document.querySelector('[x-show="showImageModal"]').classList.remove('hidden');
            },

            // 图片切换
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

            // 分页
            prevPage() {
                if (this.page > 1 && !this.loading) {
                    this.page--;
                    this.loading = true;
                    this.fetchSpots();
                }
            },

            nextPage() {
                if (this.hasMore && !this.loading) {
                    this.page++;
                    this.loading = true;
                    this.fetchSpots();
                }
            },

            // 初始化
            init() {
                this.activeFilters = Object.assign({}, this.filters);
                this.fetchSpots();
            }
        }));
    });
</script>

<style>
    .modal-transition-enter-active,
    .modal-transition-leave-active {
        transition: opacity 0.3s;
    }

    .modal-transition-enter-from,
    .modal-transition-leave-to {
        opacity: 0;
    }
</style>

@endsection