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
        Schema::create('wechat_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nickname')->comment('昵称');
            $table->string('avatar', 1000)->comment('头像');
            $table->string('openid')->comment('openid')->unique();
            $table->string('language')->comment('语言');
            $table->string('country')->comment('国家');
            $table->string('province')->comment('身份');
            $table->string('city')->comment('城市');
            $table->tinyInteger('subscribe')->comment('用户状态 1 订阅 0 取消订阅');
            $table->integer('subscribe_time')->comment('订阅时间');
            $table->string('subscribe_scene')->comment('订阅场景 ADD_SCENE_SEARCH 公众号搜索，ADD_SCENE_ACCOUNT_MIGRATION 公众号迁移，ADD_SCENE_PROFILE_CARD 名片分享，ADD_SCENE_QR_CODE 扫描二维码，ADD_SCENE_PROFILE_LINK 图文页内名称点击，ADD_SCENE_PROFILE_ITEM 图文页右上角菜单，ADD_SCENE_PAID 支付后关注，ADD_SCENE_WECHAT_ADVERTISEMENT 微信广告，ADD_SCENE_OTHERS 其他');
            $table->string('unionid')->comment('用户平台唯一身份认证');
            $table->tinyInteger('sex')->default(1)->comment('用户状态 1 男 2 女 0 未知');
            $table->string('remark')->comment('备注');
            $table->integer('groupid')->comment('分组ID');
            $table->string('tagid_list')->comment('标签列表');
            $table->creatorId();
            $table->createdAt();
            $table->updatedAt();
            $table->deletedAt();

            $table->engine = 'InnoDB';
            $table->comment('微信用户');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wechat_users');
    }
};
