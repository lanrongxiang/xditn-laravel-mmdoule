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

        Schema::create('system_table_columns', function (Blueprint $table) {
            $table->id();
            $table->string('table_name', 50)->comment('表名');
            $table->string('column_name', 50)->comment('字段名');
            $table->updatedAt();
            $table->createdAt();
            $table->deletedAt();
            $table->engine = 'InnoDB';
            $table->comment('表字段');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::drop('system_table_columns');
    }
};
