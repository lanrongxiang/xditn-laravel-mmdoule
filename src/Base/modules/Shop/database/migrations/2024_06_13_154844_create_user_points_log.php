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
    public function up(): void
    {

        Schema::create('shop_user_points_log', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->comment('用户ID');
            $table->integer('point_num')->comment('积分');
            $table->string('type')->comment('类型:1=增加,2=减少');
            $table->string('description')->comment('描述');
            $table->string('remark')->comment('备注');
            $table->createdAt();
            $table->updatedAt();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {

    }
};
