<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostVideo extends Model
{
    //
    protected $table = 'post_video';

    // 主键
    protected $primaryKey = 'id';

    // 时间戳字段
    public $timestamps = true;
}
