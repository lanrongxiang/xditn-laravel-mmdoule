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
        if (Schema::hasTable('shop_product_sku')) {
            return;
        }

        Schema::create('shop_product_sku', function (Blueprint $table) {
            $table->increments('id')->comment('ID');
            $table->integer('product_id')->default(0)->comment('商品ID');
            //$table->integer('spec_id')->default(0)->comment('规格ID');
            $table->json('spec_values')->comment('规格值集合');
            $table->json('spec_value_ids')->comment('规格值ID');
            $table->string('images', 1000)->nullable()->comment('sku图片');
            $table->string('product_no', 100)->nullable()->unique()->comment('商品编码');
            $table->integer('price')->default(0)->comment('商品价格(分）');
            $table->integer('list_price')->default(0)->comment('划线价格(分)');
            $table->integer('cost_price')->default(0)->comment('成本价格(分)');
            $table->integer('weight')->default(0)->comment('重量(KG)');
            $table->integer('volume')->default(0)->comment('体积(m³)');
            $table->integer('stock')->default(0)->comment('库存');
            $table->integer('alert_stock')->default(0)->comment('预警库存');
            $table->createdAt();
            $table->updatedAt();
            $table->deletedAt();

            $table->engine = 'InnoDB';
            $table->comment('商品SKU');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop_product_sku');
    }
};
