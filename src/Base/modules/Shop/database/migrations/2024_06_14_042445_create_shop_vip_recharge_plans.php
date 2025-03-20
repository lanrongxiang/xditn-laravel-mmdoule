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
        if (Schema::hasTable('shop_vip_recharge_plans')) {
            return;
        }

        Schema::create('shop_vip_recharge_plans', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->comment('套餐名称');
            $table->integer('price')->comment('充值金额(分)');
            $table->integer('handsel_price')->default(0)->comment('赠送金额');
            $table->integer('sort')->comment('排序');
            $table->creatorId();
            $table->createdAt();
            $table->updatedAt();
            $table->deletedAt();

            $table->engine = 'InnoDB';
            $table->comment('会员充值套餐');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop_vip_recharge_plans');
    }
};
