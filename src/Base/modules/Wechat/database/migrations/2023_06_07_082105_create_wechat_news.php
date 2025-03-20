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
        Schema::create('wechat_news', function (Blueprint $table) {
            $table->increments('id')->comment('主键');
            $table->string('title')->comment('标题');
            $table->text('content')->comment('内容');
            $table->string('thumb_media_id')->comment('图文消息缩略图的media_id');
            $table->string('content_source_url')->comment('在图文消息页面点击“阅读原文”后的页面');
            $table->string('author')->comment('作者');
            $table->string('digest')->comment('图文消息的描述，如本字段为空，则默认抓取正文前64个字');
            $table->tinyInteger('show_cover_pic')->default(1)->comment('1 显示 2 不显示');
            $table->tinyInteger('need_open_comment')->default(2)->comment('是否打开评论:1 是 2 否');
            $table->tinyInteger('only_fans_can_comment')->default(1)->comment('是否粉丝才可评论: 1 是 2 否');
            $table->creatorId();
            $table->createdAt();
            $table->updatedAt();
            $table->deletedAt();

            $table->engine = 'InnoDB';
            $table->comment('微信图文管理');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wechat_news');
    }
};
