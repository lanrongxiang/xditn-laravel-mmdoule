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
        if (Schema::hasTable('shop_products')) {
            return;
        }

        Schema::create('shop_products', function (Blueprint $table) {
            $table->increments('id')->comment('ID');
            $table->tinyInteger('type')->default(1)->comment('商品类型:1=实物商品,2=卡密商品,3=虚拟商品');
            $table->string('title', 255)->comment('商品名称');
            $table->string('keywords', 255)->comment('关键字');
            $table->string('subtitle', 1000)->nullable()->comment('副标题');
            $table->string('images')->comment('商品主图');
            $table->string('video')->default('')->nullable()->comment('商品视频');
            $table->integer('brand_id')->default(0)->nullable()->comment('商品品牌');
            // $table->integer('category_id')->comment('商品分类');
            $table->tinyInteger('is_available')->default(1)->comment('是否上架:1=立即上架,2=放入仓库');
            $table->integer('is_schedule')->default(1)->comment('定时上架:1=是,2=否');
            $table->integer('schedule_time')->default(0)->comment('上架时间');
            $table->tinyInteger('is_specifications')->default(1)->comment('是否多规格:1=是,2=否');

            $table->string('unit', 10)->comment('商品单位');
            $table->unsignedInteger('sales')->default(0)->comment('实际销量');
            $table->unsignedInteger('virtual_sales')->default(0)->comment('虚拟销量');
            $table->unsignedInteger('sort')->default(1)->comment('商品排序');
            $table->tinyInteger('ship_type')->default(1)->comment('邮费类型:1=固定运费,2=运费模版');
            $table->integer('ship_fee')->default(0)->comment('邮费(分)');
            $table->integer('ship_template_id')->default(0)->comment('运费模版ID');

            $table->string('product_no', 100)->nullable()->unique()->comment('商品编码');
            $table->unsignedInteger('price')->default(0)->comment('商品价格(分）');
            $table->unsignedInteger('list_price')->default(0)->comment('划线价格(分)');
            $table->unsignedInteger('cost_price')->default(0)->comment('成本价格(分)');
            $table->unsignedInteger('weight')->default(0)->comment('重量(KG)');
            $table->unsignedInteger('volume')->default(0)->comment('体积(m³)');
            $table->unsignedInteger('stock')->default(0)->comment('库存');
            $table->unsignedInteger('alert_stock')->default(0)->comment('预警库存');

            $table->creatorId();
            $table->createdAt();
            $table->updatedAt();
            $table->deletedAt();

            $table->engine = 'InnoDB';
            $table->comment('商品管理表');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('商品管理');
    }
};
