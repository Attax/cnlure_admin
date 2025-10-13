<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostImage extends Model
{
    //
    protected $table = 'post_image';

    // 主键
    protected $primaryKey = 'id';

    // 时间戳字段
    public $timestamps = true;
}
