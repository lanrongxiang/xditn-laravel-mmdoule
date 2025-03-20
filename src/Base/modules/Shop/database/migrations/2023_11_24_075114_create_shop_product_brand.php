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
        if (Schema::hasTable('shop_product_brand')) {
            return;
        }

        Schema::create('shop_product_brand', function (Blueprint $table) {
            $table->increments('id')->comment('ID');
            $table->string('name', 50)->comment('品牌名称');
            $table->string('logo', 255)->comment('品牌LOGO');
            $table->integer('sort')->default(1)->comment('排序');
            $table->creatorId();
            $table->createdAt();
            $table->updatedAt();
            $table->deletedAt();

            $table->engine = 'InnoDB';
            $table->comment('商品品牌');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop_product_brand');
    }
};
