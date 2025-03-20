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
        if (Schema::hasTable('shop_category_has_products')) {
            return;
        }

        Schema::create('shop_category_has_products', function (Blueprint $table) {
            $table->integer('category_id')->comment('分类ID');
            $table->tinyInteger('level')->default(0)->comment('分类Level,从0开始，0表示顶级分类');
            $table->integer('product_id')->comment('产品ID');
            $table->createdAt();
            $table->updatedAt();

            $table->engine = 'InnoDB';
            $table->comment('商品分类关联表');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop_category_has_products');
    }
};
