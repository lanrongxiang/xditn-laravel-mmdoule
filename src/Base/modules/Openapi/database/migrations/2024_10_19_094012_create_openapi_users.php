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
        if (Schema::hasTable('openapi_users')) {
            return;
        }

        Schema::create('openapi_users', function (Blueprint $table) {
            $table->id()->comment('ID');
            $table->string('username')->comment('用户名');
            $table->string('mobile', 20)->comment('手机号');
            $table->string('password')->comment('密码');
            $table->string('company')->nullable()->comment('公司名称');
            $table->string('description')->nullable()->comment('描述');
            $table->unsignedInteger('qps')->default(100)->comment('每分钟的 QPS');
            $table->string('app_key')->comment('app key');
            $table->string('app_secret')->comment('密钥');
            $table->creatorId();
            $table->createdAt();
            $table->updatedAt();
            $table->deletedAt();

            $table->engine = 'InnoDB';
            $table->comment('openapi 用户表');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('openapi_users');
    }
};
