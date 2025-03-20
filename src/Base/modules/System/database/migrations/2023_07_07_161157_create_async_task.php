<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('system_async_task')) {
            Schema::create('system_async_task', function (Blueprint $table) {
                $table->id();
                $table->string('task')->comment('task任务对应的 class 名称');
                $table->string('params')->default('')->comment('任务所需参数');
                $table->integer('start_at')->default(0)->comment('开始时间');
                $table->tinyInteger('status')->default(1)->comment('状态:un_start=1,running=2,finished=3,error=4');
                $table->integer('time_taken')->default(0)->comment('运行耗时');
                $table->string('error')->default('')->comment('执行结果错误');
                $table->string('result')->default('')->comment('执行结果');
                $table->string('retry')->default(0)->comment('重试次数');
                $table->createdAt();
                $table->updatedAt();
                $table->deletedAt();

                $table->engine = 'InnoDB';
                $table->comment('异步任务');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
