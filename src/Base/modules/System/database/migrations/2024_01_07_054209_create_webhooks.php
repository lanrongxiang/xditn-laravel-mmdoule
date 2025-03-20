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
        if (Schema::hasTable('system_webhooks')) {
            return;
        }

        Schema::create('system_webhooks', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('platform')->default(1)->comment('平台:1=钉钉,2=飞书,3=企微');
            $table->string('webhook')->comment('webhook');
            $table->tinyInteger('status')->default(1)->comment('状态:1=开启,2=关闭');
            $table->string('secret')->default('')->comment('签名密钥');
            $table->string('msg_type', 10)->default('text')->comment('消息类型:text=文本,markdown=markdown');
            $table->string('content', 5000)->default('')->comment('消息内容');
            $table->string('event', 20)->default('')->comment('事件');
            $table->creatorId();
            $table->createdAt();
            $table->updatedAt();
            $table->deletedAt();

            $table->engine = 'InnoDB';
            $table->comment('webhook 通知');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('webhooks');
    }
};
