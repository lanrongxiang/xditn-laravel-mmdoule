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
        if (Schema::hasTable('domain_domains')) {
            return;
        }

        Schema::create('domain_domains', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('域名');
            $table->string('name_servers')->default('')->comment('域名解析服务器');
            $table->integer('expired_at')->nullable()->comment('过期时间');
            $table->string('type', '10')->nullable()->comment('type:aliyun=阿里云,qcloud=腾讯云');
            $table->string('remark')->nullable()->comment('备注');
            $table->creatorId();
            $table->createdAt();
            $table->updatedAt();
            $table->deletedAt();

            $table->engine = 'InnoDB';
            $table->comment('域名列表');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('domain_domains');
    }
};
