<?php

declare(strict_types=1);

namespace Xditn\Support\Macros;

use Illuminate\Database\Schema\Blueprint;

/**
 * 数据库结构宏扩展
 */
class BlueprintMacros
{
    public function boot(): void
    {
        // 创建时间字段宏
        Blueprint::macro('createdAt', function () {
            /** @var Blueprint $this */
            $this->unsignedInteger('created_at')->default(0)->comment('创建时间');
        });
        // 更新时间字段宏
        Blueprint::macro('updatedAt', function () {
            $this->unsignedInteger('updated_at')->default(0)->comment('更新时间');
        });
        // 删除时间字段宏
        Blueprint::macro('deletedAt', function () {
            $this->unsignedInteger('deleted_at')->default(0)->comment('删除时间'); // SoftDeletes 会自动添加
        });
        // 状态字段宏
        Blueprint::macro('status', function () {
            $this->unsignedInteger('status')->default(1)->comment('状态');
        });
        // 创建者ID字段宏
        Blueprint::macro('creatorId', function () {
            $this->unsignedBigInteger('creator_id')->default(0)->comment('创建者ID');
        });
        // Unix时间戳字段宏
        Blueprint::macro('unixTimestamp', function () {
            $this->unsignedInteger('timestamp')->comment('Unix 时间戳'); // 不需要默认值
        });
        // 父级ID字段宏
        Blueprint::macro('parentId', function () {
            $this->unsignedInteger('parent_id')->default(0)->comment('父级ID');
        });
        // 排序字段宏
        Blueprint::macro('sort', function () {
            $this->unsignedInteger('sort')->default(1)->comment('排序字段');
        });
    }
}
