<?php

namespace Xditn\Commands;

use BackedEnum;
use DateTimeInterface;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Xditn\Base\XditnModel;
use Xditn\MModule;

/**
 * 将权限菜单按模块导出到各自的 Seeder。
 */
class ExportMenuCommand extends XditnCommand
{
    protected $signature = 'xditn:export:menu
                            {modules?* : 仅导出指定模块，不传则导出全部模块菜单}';

    protected $description = '一键导出全部模块菜单到对应模块的 Seeder';

    public function handle(): int
    {
        $requestedModules = collect($this->argument('modules'))
            ->map(fn (string $module) => strtolower($module))
            ->filter()
            ->unique()
            ->values();

        $availableModules = $this->createModel()
            ->newQuery()
            ->whereNotNull('module')
            ->where('module', '<>', '')
            ->distinct()
            ->orderBy('module')
            ->pluck('module')
            ->map(fn (string $module) => strtolower($module));

        $modules = $requestedModules->isNotEmpty() ? $requestedModules : $availableModules;

        if ($modules->isEmpty()) {
            $this->warn('permissions 表中没有可导出的模块菜单。');

            return self::SUCCESS;
        }

        $exported = [];
        $skipped = [];

        foreach ($modules as $module) {
            if (! MModule::isModulePathExist($module)) {
                $this->warn("[{$module}] 模块目录不存在，已跳过。");
                $skipped[] = [$module, '模块目录不存在'];

                continue;
            }

            $menus = $this->menusFor($module);
            if ($menus->isEmpty()) {
                $this->warn("[{$module}] 没有菜单数据，已跳过。");
                $skipped[] = [$module, '没有菜单数据'];

                continue;
            }

            $path = $this->exportSeed($module, $this->normalizeValue($menus));
            $exported[] = [$module, $menus->count(), MModule::getModuleRelativePath($path)];
            $this->info("[{$module}] 已导出 {$menus->count()} 个根菜单到 {$path}");
        }

        if ($exported !== []) {
            $this->newLine();
            $this->table(['模块', '根菜单数', 'Seeder'], $exported);
        }

        $this->newLine();
        $this->info(sprintf('导出完成：成功 %d 个模块，跳过 %d 个模块。', count($exported), count($skipped)));

        return self::SUCCESS;
    }

    /**
     * 获取单个模块菜单。跨模块父节点不会写入当前 Seeder，子菜单会提升为根节点。
     */
    protected function menusFor(string $module): Collection
    {
        $menus = $this->createModel()
            ->newQuery()
            ->where('module', $module)
            ->orderBy('id')
            ->get();

        $ids = $menus->pluck('id')->map(fn ($id) => (int) $id)->all();

        $menus->each(function (XditnModel $menu) use ($ids): void {
            if ((int) $menu->parent_id !== 0 && ! in_array((int) $menu->parent_id, $ids, true)) {
                $menu->setAttribute('parent_id', 0);
            }
        });

        return $menus->toTree();
    }

    protected function normalizeValue(mixed $value): mixed
    {
        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if ($value instanceof Arrayable) {
            $value = $value->toArray();
        }

        if ($value instanceof Collection) {
            $value = $value->all();
        }

        if (is_array($value)) {
            return array_map(fn (mixed $item) => $this->normalizeValue($item), $value);
        }

        return $value;
    }

    protected function exportSeed(string $module, array $menus): string
    {
        $stub = File::get(__DIR__.DIRECTORY_SEPARATOR.'stubs'.DIRECTORY_SEPARATOR.'menuSeeder.stub');
        $class = Str::studly($module).'MenusSeeder';
        $data = 'return '.var_export($menus, true).';';

        $stub = str_replace(['{CLASS}', '{menus}'], [$class, $data], $stub);

        $path = MModule::getModuleSeederPath($module).$class.'.php';
        File::put($path, $stub);

        return $path;
    }

    protected function createModel(): XditnModel
    {
        return new class extends XditnModel
        {
            protected $table = 'permissions';
        };
    }
}
