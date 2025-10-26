<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fishing_spots', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // 钓场名称
            $table->string('address'); // 地址
            $table->decimal('latitude', 10, 8)->nullable(); // 纬度
            $table->decimal('longitude', 11, 8)->nullable(); // 经度
            $table->text('description')->nullable(); // 描述
            $table->string('contact_name')->nullable(); // 联系人姓名
            $table->string('contact_phone')->nullable(); // 联系电话
            $table->string('opening_hours')->nullable(); // 营业时间
            $table->decimal('price', 10, 2)->nullable(); // 价格
            $table->integer('status')->default(0); // 状态：0-待审核，1-已审核，2-已拒绝
            $table->integer('business_status')->default(1); // 营业状态：0-休息，1-营业
            $table->json('image_urls')->nullable(); // 图片URLs数组
            $table->json('facilities')->nullable(); // 设施数组
            $table->json('fish_species')->nullable(); // 鱼类品种数组
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // 关联到用户表
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fishing_spots');
    }
};