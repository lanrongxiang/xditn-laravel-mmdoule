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
        if (Schema::hasTable('shop_category')) {
            return;
        }

        Schema::create('shop_category', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parent_id')->default(0)->comment('父级ID');
            $table->string('name', 20)->comment('分类名称');
            $table->string('icon')->comment('分类图标');
            $table->integer('sort')->default(1)->comment('排序');
            $table->tinyInteger('status')->default(1)->comment('状态:1=启用,2=禁用');
            $table->string('title', 100)->nullable()->comment('seo标题');
            $table->string('keywords', 255)->nullable()->comment('seo关键字');
            $table->string('descriptions', 1000)->nullable()->comment('seo描述');
            $table->creatorId();
            $table->createdAt();
            $table->updatedAt();
            $table->deletedAt();

            $table->engine = 'InnoDB';
            $table->comment('商品分类');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop_category');
    }
};
