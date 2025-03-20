<?php

declare(strict_types=1);

namespace Xditn\Base\modules\Develop\Support\Generate\Create;

use Illuminate\Support\Facades\DB;
use Xditn\Base\modules\Develop\Support\Generate\Exception\MenuCreateFailException;
use Xditn\Base\modules\Permissions\Enums\MenuType;
use Xditn\Base\modules\Permissions\Models\Permissions;
use Xditn\Facade\Module;

/**
 * 自动创建菜单
 */
class Menu
{
    public function __construct(
        public readonly array $gen
    ) {
    }

    /**
     * 菜单生成
     */
    public function generate(): mixed
    {
        // 如果设置了名称
        if ($this->gen['menu']) {
            return DB::transaction(function () {
                $topMenu = Permissions::where('module', $this->gen['module'])->first();
                // 如果系统模块没有顶级菜单，则需要创建顶级菜单
                if (! $topMenu) {
                    $module = Module::show($this->gen['module']);
                    $topMenuId = app(Permissions::class)->storeBy([
                        'component' => 'BasicLayout',
                        'hidden' => 1,
                        'keepalive' => 1,
                        'module' => $this->gen['module'],
                        'parent_id' => 0,
                        'permission_name' => $module['title'],
                        'route' => '/'.$this->gen['module'],
                        'sort' => 1,
                        'type' => MenuType::Top->value,
                    ]);
                } else {
                    $topMenuId = $topMenu->id;
                }
                if ($topMenuId) {
                    $controller = lcfirst($this->gen['controller']);
                    if (Permissions::where('parent_id', $topMenuId)
                        ->where('permission_name', $this->gen['menu'])
                        ->first()
                    ) {
                        throw new MenuCreateFailException('文件创建成功，但是由于存在下有相同菜单，创建菜单失败，请手动添加');
                    }
                    $id = app(Permissions::class)->storeBy([
                        'component' => '/'.$this->gen['module'].'/'.$controller.'/index.vue',
                        'hidden' => 1,
                        'keepalive' => 1,
                        'module' => $this->gen['module'],
                        'parent_id' => $topMenuId,
                        'permission_name' => $this->gen['menu'],
                        'permission_mark' => $controller,
                        'route' => $controller,
                        'sort' => 1,
                        'type' => MenuType::Menu->value,
                    ]);

                    // 生成 actions
                    app(Permissions::class)->storeBy([
                        'actions' => true,
                        'parent_id' => $id,
                        'type' => MenuType::Action->value(),
                    ]);
                }
            });
        }

        return false;
    }
}
