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

        Schema::create('system_role_has_columns', function (Blueprint $table) {
            $table->id();
            $table->integer('role_id')->comment('角色名ID');
            $table->integer('table_column_id')->comment('字段名ID');
            $table->tinyInteger('type')->default(1)->comment('授权类型:1=可读,2=可写');
            $table->engine = 'InnoDB';
            $table->comment('角色授权字段');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::drop('system_role_has_columns');
    }
};
