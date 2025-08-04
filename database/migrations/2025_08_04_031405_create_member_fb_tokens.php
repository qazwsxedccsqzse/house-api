<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 用戶有的 fb 粉專 token or group token
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('member_fb_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_id')->comment('用戶ID');
            $table->string('token')->comment('粉專或群組的 token');
            $table->unsignedTinyInteger('type')->comment('粉專或群組 1: 粉專 2: 群組');
            $table->string('expired_at')->comment('token 過期時間');
            $table->datetimes();

            $table->index('member_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_fb_tokens');
    }
};
