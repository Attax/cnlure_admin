<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'user';

    // 主键
    protected $primaryKey = 'id';

    // 时间戳字段
    public $timestamps = true;

    // 可填充字段
    protected $fillable = [
        'name',
        'email',
        'password',
        'type'
    ];

    // 定义隐藏字段
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // 类型转换
    protected $casts = [
        'type' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // 关联到钓场
    public function fishingSpots()
    {
        return $this->hasMany(FishingSpot::class, 'user_id');
    }

    // 判断是否为企业用户
    public function isEnterprise()
    {
        return $this->type === 1;
    }

    /**
     * 自定义日期时间序列化格式
     * 将时间戳从ISO 8601格式(Y-m-d\TH:i:s.000000Z)改为更易读的格式
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
