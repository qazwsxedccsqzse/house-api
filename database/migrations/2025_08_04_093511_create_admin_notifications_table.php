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
        Schema::create('admin_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('type')->nullable()->default(1)->comment('通知類型: 1: 系統通知');
            $table->unsignedBigInteger('user_id')->nullable()->comment('用戶ID');
            $table->string('message')->comment('通知內容');
            $table->unsignedTinyInteger('status')->default(1)->comment('通知狀態: 1: 未讀 2: 已讀');
            $table->datetimes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_notifications');
    }
};
