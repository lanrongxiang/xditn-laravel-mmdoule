<?php

declare(strict_types=1);

namespace Xditn\Support\Module\Driver;

use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Xditn\Contracts\ModuleRepositoryInterface;
use Xditn\Enums\Status;
use Xditn\Exceptions\FailedException;

/**
 * DatabaseDriver
 */
class DatabaseDriver implements ModuleRepositoryInterface
{
    protected Model $model;

    public function __construct()
    {
        $this->model = $this->createModuleModel();
    }

    /**
     * 获取所有模块
     *
     * @param  array  $search
     * @return Collection
     */
    public function all(array $search = []): Collection
    {
        return $this->model::query()
                           ->when($search['title'] ?? false, fn ($query) => $query->where('title', 'like', '%'.$search['title'].'%'))
                           ->get();
    }

    /**
     * 创建模块
     *
     * @param  array  $module
     * @return bool|int
     */
    public function create(array $module): bool|int
    {
        $this->hasSameModule($module);

        return $this->model->newQuery()->insert([
            'title' => $module['title'],
            'name' => $module['name'],
            'path' => $module['path'],
            'description' => $module['description'] ?? $module['desc'] ?? '',
            'keywords' => $module['keywords'] ?? '',
            'version' => $module['version'] ?? '1.0.0',
            'status' => Status::Enable->value(),
            'created_at' => time(),
            'updated_at' => time(),
        ]);
    }

    /**
     * 获取模块信息
     *
     * @param  string  $name
     * @return Collection
     */
    public function show(string $name): Collection
    {
        return $this->model->where('name', $name)->first();
    }

    /**
     * 更新模块信息
     *
     * @param  string  $name
     * @param  array  $module
     * @return bool|int
     */
    public function update(string $name, array $module): bool|int
    {
        return $this->model->where('name', $name)->update([
            'title' => $module['title'],
            'name' => $module['path'],
            'path' => $module['path'],
            'description' => $module['desc'],
            'keywords' => $module['keywords'],
        ]);
    }

    /**
     * 删除模块
     *
     * @param  string  $name
     * @return bool|int
     */
    public function delete(string $name): bool|int
    {
        return $this->model->where('name', $name)->delete();
    }

    /**
     * 禁用或启用模块
     *
     * @param  string  $name
     * @return bool|int
     */
    public function disOrEnable(string $name): bool|int
    {
        $module = $this->show($name);
        $module->status = Status::Enable->assert($module->status)
            ? Status::Disable->value()
            : Status::Enable->value();

        return $module->save();
    }

    /**
     * 获取启用的模块
     *
     * @return Collection
     */
    public function getEnabled(): Collection
    {
        return $this->model->where('status', Status::Enable->value())->get();
    }

    /**
     * 检查模块是否启用
     *
     * @param  string  $moduleName
     * @return bool
     */
    public function enabled(string $moduleName): bool
    {
        return $this->getEnabled()->pluck('name')->contains($moduleName);
    }

    /**
     * 检查是否存在相同模块
     *
     * @param  array  $module
     * @return void
     */
    protected function hasSameModule(array $module): void
    {
        if ($this->model->query()->where('name', $module['name'])->exists()) {
            throw new FailedException(sprintf('Module [%s] has been created', $module['name']));
        }
    }

    /**
     * 创建模块模型
     *
     * @return Model
     */
    protected function createModuleModel(): Model
    {
        return new class extends Model
        {
            protected $table;

            public function __construct(array $attributes = [])
            {
                parent::__construct($attributes);
                $this->table = Container::getInstance()->make('config')->get('xditn.module.driver.table_name');
            }
        };
    }
}
