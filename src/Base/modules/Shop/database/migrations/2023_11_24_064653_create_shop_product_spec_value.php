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
        if (Schema::hasTable('shop_product_spec_value')) {
            return;
        }

        Schema::create('shop_product_spec_value', function (Blueprint $table) {
            $table->increments('id')->comment('ID');
            $table->string('value', 255)->comment('规格值');
            $table->integer('spec_id')->comment('规则ID');
            $table->creatorId();
            $table->createdAt();
            $table->updatedAt();
            $table->deletedAt();

            $table->engine = 'InnoDB';
            $table->comment('规格值');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop_product_spec_value');
    }
};
