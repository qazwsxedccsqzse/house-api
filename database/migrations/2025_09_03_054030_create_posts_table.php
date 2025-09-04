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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_id')->nullable(false)->comment('會員ID');
            $table->unsignedBigInteger('platform')->default(1)->nullable()->comment('平台: 1: fb, 2: thread');
            $table->unsignedBigInteger('page_id')->nullable(false)->comment('粉絲頁ID, for fb');
            $table->string('post_id', 200)->nullable()->comment('貼文ID');
            $table->text('post_text')->nullable(false)->comment('貼文內容');
            $table->string('post_image')->comment('貼文圖片');
            $table->string('post_video')->comment('貼文影片');
            $table->unsignedTinyInteger('status')->default(1)->comment('貼文狀態: 1: 排程中 2: 已發佈 3: 已下架');
            $table->datetime('post_at')->nullable(false)->comment('發送時間 (台灣時區)');
            $table->datetimes();
            $table->softDeletesDatetime();

            $table->index(['member_id', 'platform', 'page_id', 'status']);
            $table->index(['status', 'post_at', 'id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
