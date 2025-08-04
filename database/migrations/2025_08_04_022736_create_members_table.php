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
            $table->string('email')->unique(); // 信箱
            $table->string('password'); // 密碼
            $table->string('phone')->nullable(false); // 電話
            $table->string('estate_broker_number')->nullable(false); // 營業員證號
            $table->unsignedTinyInteger('status')->default(1); // 狀態 1: 正常 0: 停用
            $table->string('line_id')->nullable(); // Line ID
            $table->string('line_picture')->nullable(); // Line 頭像
            $table->string('plan_id')->nullable(); // 方案ID
            $table->dateTime('plan_start_date')->nullable(); // 方案開始日期
            $table->dateTime('plan_end_date')->nullable(); // 方案結束日期
            $table->datetimes(); // 建立時間與更新時間
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
