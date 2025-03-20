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
        if (Schema::hasTable('members')) {
            return;
        }

        Schema::create('members', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username', 50)->comment('用户名');
            $table->string('email', 100)->nullable()->unique()->comment('邮箱');
            $table->string('mobile', 20)->unique()->comment('手机号');
            $table->string('password', 255)->nullable()->comment('密码');
            $table->string('avatar', 255)->nullable()->comment('头像地址');
            $table->integer('address_id')->nullable()->comment('默认地址');
            $table->string('from', 20)->default('miniprogram')->comment('注册来源:pc=pc,app=app,miniprogram=小程序,admin=后台添加');
            $table->tinyInteger('status')->default(1)->comment('状态:1=正常,2=禁用');
            $table->integer('last_login_at')->default(0)->comment('最近登录时间');
            $table->creatorId();
            $table->createdAt();
            $table->updatedAt();
            $table->deletedAt();

            $table->engine = 'InnoDB';
            $table->comment('会员表');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('members');
    }
};
