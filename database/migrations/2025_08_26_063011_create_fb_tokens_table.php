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
        Schema::create('fb_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_id');
            $table->unsignedTinyInteger('type')->comment('1:page, 2:group, 3:user');
            $table->string('fb_id')->comment('fb 粉絲頁或是 group id');
            $table->string('name')->comment('fb 粉絲頁或是 group 名稱');
            $table->string('access_token', 500)->comment('fb 粉絲頁或是 group 的 access token');
            $table->dateTime('expires_at')->comment('fb 粉絲頁或是 group 的 access token 過期時間');
            $table->dateTimes();

            $table->index(['member_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fb_tokens');
    }
};
