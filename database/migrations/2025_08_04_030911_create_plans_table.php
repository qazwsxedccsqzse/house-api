<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 方案表
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // 方案名稱
            $table->string('description'); // 方案描述
            $table->unsignedTinyInteger('status')->default(1); // 狀態 1: 正常 0: 停用
            $table->unsignedInteger('days'); // 方案可用天數
            $table->unsignedInteger('price'); // 方案價格
            $table->datetimes(); // 建立時間與更新時間
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
