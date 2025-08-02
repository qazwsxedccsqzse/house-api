<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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
            $table->string('email')->unique()->comment('管理員信箱');
            $table->string('username')->unique()->comment('管理員帳號');
            $table->string('password')->comment('管理員密碼');
            $table->unsignedBigInteger('role_id')->comment('角色ID');
            $table->foreign('role_id')->references('id')->on('roles');
            $table->timestamps();
        });

        // add table comment
        DB::statement('ALTER TABLE admins COMMENT = "管理員資料表"');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
