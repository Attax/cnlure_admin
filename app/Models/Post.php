<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    // 使用软删除
    // use SoftDeletes;

    // 帖子模型
    protected $table = 'post_meta';

    // 可批量赋值的属性
    protected $fillable = [
        'title',
        'content',
        'summary',
        'status',
        'user_id',
    ];

    /**
     * 自定义日期时间序列化格式
     * 将时间戳从ISO 8601格式(Y-m-d\TH:i:s.000000Z)改为更易读的格式
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }


    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function images()
    {
        return $this->hasMany(PostImage::class, 'post_id', 'id');
    }

    public function videos()
    {
        return $this->hasMany(PostVideo::class, 'post_id', 'id');
    }
}
