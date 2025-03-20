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
        if (Schema::hasTable('system_connector_log')) {
            return;
        }

        Schema::create('system_connector_log', function (Blueprint $table) {
            $table->increments('id')->comment('ID');
            $table->string('username', 100)->comment('用户名');
            $table->string('path')->comment('接口地址');
            $table->string('method')->default('GET')->comment('请求方法');
            $table->string('user_agent')->comment('ua');
            $table->string('ip', 50)->comment('ip地址');
            $table->string('controller', 100)->comment('控制器');
            $table->string('action', 50)->comment('方法');
            $table->integer('time_taken')->comment('耗时(ms)');
            $table->integer('status_code')->comment('状态码');
            $table->tinyInteger('from')->default(1)->comment('请求来源:1=后台,2=前台');
            $table->creatorId();
            $table->integer('created_at')->comment('请求开始时间');
            $table->updatedAt();
            $table->deletedAt();

            $table->engine = 'InnoDB';
            $table->comment('接口日志');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system_connector_log');
    }
};
