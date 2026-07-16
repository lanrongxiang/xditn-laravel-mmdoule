<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $table = config('xditn.module.driver.table_name', 'admin_modules');

        // 兼容宿主项目旧迁移名 2022_11_14_034127_module，避免重复建表冲突
        if (Schema::hasTable($table)) {
            return;
        }

        Schema::create($table, function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->comment('模块标题');
            $table->string('name')->comment('模块名称');
            $table->string('path', 20)->comment('模块目录');
            $table->string('description')->comment('模块描述');
            $table->string('keywords')->comment('模块关键字');
            $table->string('version', 20)->comment('模块版本号')->default('1.0.0');
            $table->tinyInteger('status')->comment('模块状态')->default(1);
            $table->unsignedInteger('created_at')->comment('创建时间')->default(0);
            $table->unsignedInteger('updated_at')->comment('更新时间')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('xditn.module.driver.table_name', 'admin_modules'));
    }
};
