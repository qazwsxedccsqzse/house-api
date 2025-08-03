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
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('管理員名稱');
            $table->string('username')->unique()->comment('管理員帳號');
            $table->string('email')->unique()->comment('管理員信箱');
            $table->string('password')->comment('管理員密碼');
            $table->tinyInteger('status')->default(1)->comment('狀態：1=啟用，0=停用');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
