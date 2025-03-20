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
        if (! Schema::hasTable('system_cron_tasks')) {
            Schema::create('system_cron_tasks', function (Blueprint $table) {
                $table->id();
                $table->string('name')->comment('定时任务名称');
                $table->string('command')->comment('定时任务命令 command 名称');
                $table->string('cycle')->comment('周期调度');
                $table->string('days')->default('')->comment('第几天, 可以填写多个');
                $table->string('start_at')->default('')->comment('开始时间，如果只有开始时间，则认为是某个时段');
                $table->string('end_at')->default('')->comment('结束时间，如果没有开始时间，则结束时间无效');
                $table->tinyInteger('is_schedule')->default(1)->comment('是否调度 默认调度 1，2 不进行调度');
                $table->tinyInteger('is_overlapping')->default(2)->comment('是否允许重复调度，1 允许 2 不允许');
                $table->tinyInteger('is_on_one_server')->default(1)->comment('是否运行在一台服务器上 默认 1 ，2 运行在多台服务器');
                $table->tinyInteger('is_run_background')->default(2)->comment('是否后台运行 1 是 2 否，默认否');
                $table->integer('run_at')->default(0)->comment('开始运行时间');
                $table->integer('run_end_at')->default(0)->comment('运行结束时间');
                $table->integer('success_times')->default(0)->comment('成功次数');
                $table->integer('failed_times')->default(0)->comment('失败次数');
                $table->tinyInteger('status')->default(1)->comment('1 未开始 2 运行中');
                $table->creatorId();
                $table->createdAt();
                $table->updatedAt();
                $table->deletedAt();

                $table->engine = 'InnoDB';
                $table->comment('定时任务');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system_cron_tasks');
    }
};
