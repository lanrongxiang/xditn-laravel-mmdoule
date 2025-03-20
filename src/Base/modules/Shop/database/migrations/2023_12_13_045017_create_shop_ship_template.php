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
        if (Schema::hasTable('shop_ship_template')) {
            return;
        }

        Schema::create('shop_ship_template', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('模版名称');
            $table->tinyInteger('bill_type')->default(1)->comment('计费方式:按件数=1,按重量=2,按体积=3');
            $table->json('delivery_areas')->comment('配送区域及运费');
            $table->integer('sort')->default(1)->comment('排序');
            $table->creatorId();
            $table->createdAt();
            $table->updatedAt();
            $table->deletedAt();

            $table->engine = 'InnoDB';
            $table->comment('运费模版');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop_ship_template');
    }
};
