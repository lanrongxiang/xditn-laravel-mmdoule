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
        if (Schema::hasTable('asd')) { return; }

        Schema::create('asd', function (Blueprint $table) {
            $table->integer('asd');
$table->bigIncrements('zxc');
$table->creatorId();
$table->createdAt();
$table->updatedAt();
$table->deletedAt();

$table->engine='InnoDB';
$table->comment('asd');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('asd');
    }
};
