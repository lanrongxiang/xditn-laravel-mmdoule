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
        if (Schema::hasTable('shop_product_services')) {
            return;
        }

        Schema::create('shop_product_services', function (Blueprint $table) {
            $table->increments('id')->comment('ID');
            $table->string('name', 50)->comment('服务名称');
            $table->string('icon', 255)->comment('图标');
            $table->string('description')->nullable()->comment('服务说明');
            $table->creatorId();
            $table->createdAt();
            $table->updatedAt();
            $table->deletedAt();

            $table->engine = 'InnoDB';
            $table->comment('商品服务');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop_product_services');
    }
};
