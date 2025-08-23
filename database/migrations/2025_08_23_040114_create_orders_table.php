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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_id')->comment('會員ID');
            $table->unsignedBigInteger('plan_id')->comment('方案ID');
            $table->string('status')->comment('訂單狀態');
            $table->decimal('price', 10, 2)->comment('訂單金額');
            $table->dateTime('start_date')->comment('開始日期');
            $table->dateTime('end_date')->comment('結束日期');
            $table->timestamps();
            $table->softDeletes();

            $table->index('member_id');
            $table->index('plan_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
