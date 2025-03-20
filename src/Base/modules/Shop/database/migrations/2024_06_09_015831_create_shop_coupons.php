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
        if (Schema::hasTable('shop_coupons')) {
            return;
        }

        Schema::create('shop_coupons', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->comment('优惠券名称');
            $table->tinyInteger('type')->default(1)->comment('优惠卷类型:1=满减券,2=折扣券');
            $table->integer('reduce_price')->default(0)->comment('减免金额');
            $table->tinyInteger('discount')->default(0)->comment('折扣率');
            $table->integer('min_price')->default(0)->comment('最低消费金额');
            $table->tinyInteger('expire_type')->default(1)->comment('到期类型:1=领取后生效,2=固定时间');
            $table->unsignedTinyInteger('validaty')->default(0)->comment('有效期(天)');
            $table->unsignedInteger('start_at')->default(0)->comment('领取开始时间');
            $table->unsignedInteger('end_at')->default(0)->comment('领取结束时间');
            $table->tinyInteger('scope')->default(1)->comment('适用范围:1=全部商品,2=指定商品,3=排除商品');
            $table->json('scope_data')->nullable()->comment('适用范围数据');
            $table->integer('total_num')->default(0)->comment('总数量');
            $table->integer('receive_num')->default(0)->comment('领取总数量');
            $table->string('describe', 2000)->comment('优惠券描述');
            $table->tinyInteger('status')->default(1)->comment('状态:1=显示,2=隐藏');
            $table->unsignedSmallInteger('sort')->default(1)->comment('排序');
            $table->creatorId();
            $table->createdAt();
            $table->updatedAt();
            $table->deletedAt();

            $table->engine = 'InnoDB';
            $table->comment('优惠券');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop_coupons');
    }
};
