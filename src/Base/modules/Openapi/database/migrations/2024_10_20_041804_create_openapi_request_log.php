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
        if (Schema::hasTable('openapi_request_log')) {
            return;
        }

        Schema::create('openapi_request_log', function (Blueprint $table) {
            $table->id();
            $table->uuid('request_id')->comment('请求id');
            $table->string('app_key')->comment('app key');
            $table->json('data')->comment('请求数据');
            $table->createdAt();
            $table->updatedAt();

            $table->engine = 'InnoDB';
            $table->comment('openapi 请求日志');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('openapi_request_log');
    }
};
