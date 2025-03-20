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
        Schema::create('shop_product_has_specs', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('product_id')->comment('产品ID');

            $table->integer('spec_id')->comment('规格ID');

            $table->integer('spec_value_id')->comment('规格值ID');
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
