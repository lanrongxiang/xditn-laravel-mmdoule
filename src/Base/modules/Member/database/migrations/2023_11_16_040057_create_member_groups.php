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
        if (Schema::hasTable('member_groups')) {
        return;
        }

        Schema::create('member_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50)->comment('组名');
            $table->string('description', 255)->comment('分组描述');
            $table->tinyInteger('status')->default(1)->comment('状态:1=开启,2=关闭');
            $table->creatorId();
            $table->createdAt();
            $table->updatedAt();
            $table->deletedAt();

            $table->engine = 'InnoDB';
            $table->comment('会员分组');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('member_groups');
    }
};
