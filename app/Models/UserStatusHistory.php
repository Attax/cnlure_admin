<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'user_status_histories';

    protected $fillable = [
        'user_id',
        'operator_id',
        'reason',
        'status_before',
        'status_after',
        'ban_time',
        'unban_time',
        'is_active'
    ];

    // 与用户的关联关系
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // 与操作人的关联关系
    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }
}