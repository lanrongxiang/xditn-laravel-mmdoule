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
        if (Schema::hasTable('shop_product_info')) {
            return;
        }

        Schema::create('shop_product_info', function (Blueprint $table) {
            $table->increments('id')->comment('ID');
            $table->integer('product_id')->comment('产品ID');
            $table->text('content')->nullable()->comment('内容');
            $table->json('params')->nullable()->comment('商品参数');
            $table->engine = 'InnoDB';
            $table->comment('产品信息(冗余信息）');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop_product_info');
    }
};
