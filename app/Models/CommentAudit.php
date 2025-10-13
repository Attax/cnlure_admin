<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommentAudit extends Model
{
    // 评论审核表
    protected $table = 'comment_audit';

    // 主键
    protected $primaryKey = 'id';

    // 时间戳字段
    public $timestamps = true;

    // 可批量赋值的属性
    protected $fillable = [
        'comment_id',
        'audit_status',
        'user_id',
    ];
}
