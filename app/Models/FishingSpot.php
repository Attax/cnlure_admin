<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FishingSpot extends Model
{
    protected $fillable = [
        'name',
        'address',
        'latitude',
        'longitude',
        'description',
        'contact_name',
        'contact_phone',
        'opening_hours',
        'price',
        'status',
        'business_status',
        'image_urls',
        'facilities',
        'fish_species',
        'user_id'
    ];

    protected $casts = [
        'image_urls' => 'array',
        'facilities' => 'array',
        'fish_species' => 'array',
        'status' => 'integer',
        'business_status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // 关联到用户（钓场主）
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function posts()
    {
        return $this->hasMany(Post::class, 'fishing_spot_id');
    }
}
