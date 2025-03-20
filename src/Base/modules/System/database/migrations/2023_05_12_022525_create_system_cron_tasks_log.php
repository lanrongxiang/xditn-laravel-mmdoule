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
        if (! Schema::hasTable('system_cron_tasks_log')) {
            Schema::create('system_cron_tasks_log', function (Blueprint $table) {
                $table->id();
                $table->integer('task_id')->comment('任务ID');
                $table->integer('start_at')->default(0)->comment('开始时间');
                $table->integer('end_at')->default(0)->comment('结束时间');
                $table->tinyInteger('status')->default(1)->comment('状态 1 成功 2 失败');
                $table->createdAt();
                $table->updatedAt();
                $table->deletedAt();

                $table->engine = 'InnoDB';
                $table->comment('定时任务执行日志');
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
        Schema::dropIfExists('system_cron_tasks_log');
    }
};
