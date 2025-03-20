<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('shop_seckill_activity')) {
            return;
        }

        Schema::create('shop_seckill_activity', function (Blueprint $table) {
            $table->increments('id');
            $table->date('activity_start_date')->comment('活动开始日期');
            $table->date('activity_end_date')->comment('活动结束日期');
            $table->json('activity_events')->comment('活动场次');
            $table->tinyInteger('status')->default(1)->comment('状态:1=开启,2=关闭');
            $table->integer('seckill_product_id')->comment('秒杀商品');
            $table->creatorId();
            $table->createdAt();
            $table->updatedAt();
            $table->deletedAt();

            $table->engine = 'InnoDB';
            $table->comment('秒杀活动配置');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop_seckill_activity');
    }
};
