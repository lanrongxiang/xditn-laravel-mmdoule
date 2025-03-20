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
        if (Schema::hasTable('shop_seckill_products')) {
            return;
        }

        Schema::create('shop_seckill_products', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id')->comment('商品ID');
            $table->integer('seckill_price')->comment('秒杀价格(分)');
            $table->integer('stock')->default(0)->comment('库存:如果默认 0 则使用原商品库存');
            $table->integer('limit_per_user')->default(1)->comment('限购数量');
            $table->tinyInteger('stock_reduce_type')->default(1)->comment('库存计算方式:1=下单减库存,2=付款减库存');
            $table->tinyInteger('status')->default(1)->comment('状态:1=上架,2=下架');
            $table->integer('sort')->default(1)->comment('排序');
            $table->creatorId();
            $table->createdAt();
            $table->updatedAt();
            $table->deletedAt();

            $table->engine = 'InnoDB';
            $table->comment('秒杀商品表');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('seckill_products');
    }
};
