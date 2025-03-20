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

        Schema::create('openapi_user_balance', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->integer('user_id')->comment('用户id');
            $table->unsignedInteger('balance')->default(0)->comment('用户余额');
            $table->createdAt();
            $table->updatedAt();
            $table->deletedAt();
            $table->engine = 'InnoDB';
            $table->comment('用户余额表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('openapi_user_balance');
    }
};
