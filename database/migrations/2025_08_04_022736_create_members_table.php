<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 用戶表
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // 姓名
            $table->string('email')->nullable(); // 信箱
            $table->string('password'); // 密碼
            $table->unsignedTinyInteger('status')->default(1); // 狀態 1: 正常 0: 停用
            $table->string('social_id')->nullable(); // 社交平台ID
            $table->string('social_type')->nullable(); // 社交平台類型 1: LINE, 2: Facebook
            $table->string('social_picture')->nullable(); // 社交平台頭像
            $table->string('social_name')->nullable(); // 社交平台名稱
            $table->unsignedBigInteger('plan_id')->nullable(); // 方案ID
            $table->timestamps(); // 建立時間與更新時間
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
