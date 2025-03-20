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

        Schema::create('shop_user_has_coupons', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->comment('用户ID');
            $table->integer('coupon_id')->comment('优惠券ID');
            $table->integer('status')->default(0)->comment('是否使用:1=是,2=否');
            $table->createdAt();
            $table->updatedAt();
            $table->engine = 'InnoDB';
            $table->comment('用户领取优惠券记录');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_user_has_coupons');
    }
};
