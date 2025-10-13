<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostAudit extends Model
{
    // 帖子审核模型
    protected $table = 'post_audit';

    // 主键
    protected $primaryKey = 'id';

    // 时间戳字段
    public $timestamps = true;

    // 可批量赋值的属性
    protected $fillable = [
        'post_id',
        'audit_status',
        'user_id',
    ];
}
