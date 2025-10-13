<?php

namespace App\Http\Controllers;

use App\Models\FishingSpot;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class FishingSpotController extends Controller
{
    /**
     * 显示钓场管理页面
     */
    public function list()
    {
        return view('fishing-spots.list');
    }

    /**
     * 获取钓场列表数据
     */
    public function getList(Request $request)
    {
        // 分页参数
        $page = (int) $request->query('page', 1);
        $pageSize = (int) $request->query('page_size', 10);
        $keyword = $request->query('keyword', '');
        $status = $request->query('status', '');

        // 构建查询
        $query = FishingSpot::query();

        // 关键词搜索（钓场名称、地址）
        if (!empty($keyword)) {
            $query->where(function (Builder $query) use ($keyword) {
                $query->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('address', 'like', '%' . $keyword . '%');
            });
        }

        // 状态筛选
        if ($status !== '') {
            $query->where('status', $status);
        }

        // 分页查询
        $paginatedSpots = $query->orderBy('created_at', 'desc')
            ->simplePaginate($pageSize, ['*'], 'page', $page);

        $spots = $paginatedSpots->items();

        return response()->json([
            'code' => 200,
            'msg' => 'success',
            'data' => $spots,
            'pagination' => [
                'current_page' => $page,
                'page_size' => $pageSize,
                'has_more' => $paginatedSpots->hasMorePages()
            ]
        ]);
    }

    /**
     * 获取钓场详情
     */
    public function item(Request $request, $id)
    {
        $spot = FishingSpot::find($id);
        if (!$spot) {
            return response()->json([
                'code' => 1,
                'message' => '钓场不存在'
            ]);
        }

        return response()->json([
            'code' => 0,
            'message' => 'success',
            'data' => $spot
        ]);
    }

    /**
     * 创建钓场
     */
    public function store(Request $request)
    {
        // 验证请求数据
        $request->validate([
            'name' => 'required|max:100',
            'address' => 'required|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'description' => 'nullable|max:1000',
            'contact_phone' => 'nullable|regex:/^1[3-9]\d{9}$/',
            'opening_hours' => 'nullable|max:100',
            'price' => 'nullable|numeric|min:0',
            'status' => 'required|in:0,1',
            'image_urls' => 'nullable|array',
            'facilities' => 'nullable|array',
            'fish_species' => 'nullable|array'
        ]);

        // 创建钓场
        $spot = FishingSpot::create([
            'name' => $request->input('name'),
            'address' => $request->input('address'),
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
            'description' => $request->input('description'),
            'contact_phone' => $request->input('contact_phone'),
            'opening_hours' => $request->input('opening_hours'),
            'price' => $request->input('price'),
            'status' => $request->input('status'),
            'image_urls' => $request->input('image_urls', []),
            'facilities' => $request->input('facilities', []),
            'fish_species' => $request->input('fish_species', [])
        ]);

        return response()->json([
            'code' => 0,
            'message' => '创建成功',
            'data' => $spot
        ]);
    }

    /**
     * 更新钓场信息
     */
    public function update(Request $request, $id)
    {
        $spot = FishingSpot::find($id);
        if (!$spot) {
            return response()->json([
                'code' => 1,
                'message' => '钓场不存在'
            ]);
        }

        // 验证请求数据
        $request->validate([
            'name' => 'required|max:100',
            'address' => 'required|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'description' => 'nullable|max:1000',
            'contact_phone' => 'nullable|regex:/^1[3-9]\d{9}$/',
            'opening_hours' => 'nullable|max:100',
            'price' => 'nullable|numeric|min:0',
            'status' => 'required|in:0,1',
            'image_urls' => 'nullable|array',
            'facilities' => 'nullable|array',
            'fish_species' => 'nullable|array'
        ]);

        // 更新钓场信息
        $spot->update([
            'name' => $request->input('name'),
            'address' => $request->input('address'),
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
            'description' => $request->input('description'),
            'contact_phone' => $request->input('contact_phone'),
            'opening_hours' => $request->input('opening_hours'),
            'price' => $request->input('price'),
            'status' => $request->input('status'),
            'image_urls' => $request->input('image_urls', []),
            'facilities' => $request->input('facilities', []),
            'fish_species' => $request->input('fish_species', [])
        ]);

        return response()->json([
            'code' => 0,
            'message' => '更新成功',
            'data' => $spot
        ]);
    }

    /**
     * 删除钓场
     */
    public function destroy(Request $request, $id)
    {
        $spot = FishingSpot::find($id);
        if (!$spot) {
            return response()->json([
                'code' => 1,
                'message' => '钓场不存在'
            ]);
        }

        // 检查是否有关联的帖子
        if ($spot->posts()->exists()) {
            return response()->json([
                'code' => 1,
                'message' => '该钓场下还有关联的帖子，无法删除'
            ]);
        }

        // 删除钓场
        $spot->delete();

        return response()->json([
            'code' => 0,
            'message' => '删除成功'
        ]);
    }
}
