<?php

namespace Database\Seeders;

use App\Models\FishingSpot;
use Illuminate\Database\Seeder;

class FishingSpotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 创建5条钓场测试数据
        $spots = [
            [
                'name' => '青山水库',
                'address' => '浙江省杭州市临安区太湖源镇',
                'latitude' => 30.2701,
                'longitude' => 119.8723,
                'description' => '风景优美的高山水库，水质清澈，鱼类资源丰富',
                'contact_phone' => '13800138001',
                'opening_hours' => '06:00-18:00',
                'price' => '100元/天',
                'status' => 1,
                'image_urls' => [
                    'https://picsum.photos/id/10/800/600',
                    'https://picsum.photos/id/11/800/600',
                    'https://picsum.photos/id/12/800/600'
                ],
                'facilities' => ['停车场', '休息区', '洗手间', '小卖部'],
                'fish_species' => ['草鱼', '鲤鱼', '鲫鱼', '鲈鱼', '鳜鱼']
            ],
            [
                'name' => '西湖垂钓中心',
                'address' => '浙江省杭州市西湖区北山路',
                'latitude' => 30.2578,
                'longitude' => 120.1480,
                'description' => '位于西湖边的专业垂钓场所，环境优美',
                'contact_phone' => '13900139002',
                'opening_hours' => '08:00-20:00',
                'price' => '150元/天',
                'status' => 1,
                'image_urls' => [
                    'https://picsum.photos/id/13/800/600',
                    'https://picsum.photos/id/14/800/600'
                ],
                'facilities' => ['停车场', '餐厅', '休息区', '洗手间'],
                'fish_species' => ['鲤鱼', '草鱼', '鲢鱼', '鳙鱼']
            ],
            [
                'name' => '千岛湖钓鱼基地',
                'address' => '浙江省杭州市淳安县千岛湖镇',
                'latitude' => 29.5423,
                'longitude' => 119.0352,
                'description' => '国家级水利风景区，湖光山色，鱼类种类繁多',
                'contact_phone' => '13700137003',
                'opening_hours' => '05:00-21:00',
                'price' => '200元/天',
                'status' => 1,
                'image_urls' => [
                    'https://picsum.photos/id/15/800/600',
                    'https://picsum.photos/id/16/800/600',
                    'https://picsum.photos/id/17/800/600',
                    'https://picsum.photos/id/18/800/600'
                ],
                'facilities' => ['停车场', '餐厅', '住宿', '休息区', '洗手间', '钓鱼用品店'],
                'fish_species' => ['鳙鱼', '草鱼', '鲤鱼', '鲫鱼', '鳜鱼', '翘嘴红鲌']
            ],
            [
                'name' => '西溪湿地钓场',
                'address' => '浙江省杭州市西湖区西溪湿地公园',
                'latitude' => 30.2498,
                'longitude' => 120.0912,
                'description' => '湿地生态系统，原生态钓鱼体验',
                'contact_phone' => '13600136004',
                'opening_hours' => '07:00-19:00',
                'price' => '120元/天',
                'status' => 0,
                'image_urls' => [
                    'https://picsum.photos/id/19/800/600',
                    'https://picsum.photos/id/20/800/600'
                ],
                'facilities' => ['停车场', '休息区', '洗手间'],
                'fish_species' => ['鲫鱼', '鲤鱼', '鳊鱼', '黑鱼']
            ],
            [
                'name' => '钱塘江钓鱼码头',
                'address' => '浙江省杭州市滨江区钱塘江畔',
                'latitude' => 30.2102,
                'longitude' => 120.2288,
                'description' => '钱塘江畔专业钓鱼码头，适合海竿钓法',
                'contact_phone' => '13500135005',
                'opening_hours' => '04:00-10:00',
                'price' => '80元/天',
                'status' => 1,
                'image_urls' => [
                    'https://picsum.photos/id/21/800/600',
                    'https://picsum.photos/id/22/800/600',
                    'https://picsum.photos/id/23/800/600'
                ],
                'facilities' => ['停车场', '休息区'],
                'fish_species' => ['鲈鱼', '鲻鱼', '鳊鱼', '鲤鱼']
            ]
        ];

        foreach ($spots as $spot) {
            FishingSpot::create($spot);
        }

        echo "成功创建5条钓场测试数据！\n";
    }
}