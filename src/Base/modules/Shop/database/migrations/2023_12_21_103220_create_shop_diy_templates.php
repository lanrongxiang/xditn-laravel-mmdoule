<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('shop_diy_templates')) {
            return;
        }

        Schema::create('shop_diy_templates', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('模版名称');
            $table->integer('type')->default(1)->comment('模版类型:1=首页,2=会员中心');
            $table->json('content')->comment('定制内容');
            $table->creatorId();
            $table->createdAt();
            $table->updatedAt();
            $table->deletedAt();

            $table->engine = 'InnoDB';
            $table->comment('DIY模版');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop_diy_templates');
    }
};
