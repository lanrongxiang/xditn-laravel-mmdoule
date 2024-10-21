<?php

declare(strict_types=1);

namespace Xditn\Support\Module\Driver;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Xditn\Contracts\ModuleRepositoryInterface;
use Xditn\Exceptions\FailedException;
use Xditn\MModule;

/**
 * FileDriver
 */
class FileDriver implements ModuleRepositoryInterface
{
    protected string $moduleJson;

    public function __construct()
    {
        $this->moduleJson = storage_path('app') . DIRECTORY_SEPARATOR . 'modules.json';
    }

    public function all(array $search = []): Collection
    {
        if (!File::exists($this->moduleJson) || !Str::length(File::get($this->moduleJson))) {
            return Collection::make([]);
        }
        $modules = Collection::make(json_decode(File::get($this->moduleJson), true))->values();
        return $search['title'] ?? '' ? $modules->filter(
            fn($module) => Str::of($module['title'])->contains($search['title'])
        ) : $modules;
    }

    public function create(array $module): bool
    {
        $modules = $this->all();
        $this->hasSameModule($module, $modules);
        $module = array_merge([
            'provider' => sprintf('\\%s', MModule::getModuleServiceProvider($module['path'])),
            'version'  => '1.0.0',
            'enable'   => true,
        ], $module);
        $this->removeDirs($module);
        File::put(
            $this->moduleJson,
            $modules->push($module)->values()->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
        return true;
    }

    public function show(string $name): Collection
    {
        $module = $this->all()->first(fn($module) => Str::of($module['name'])->exactly($name));
        return $module ? Collection::make($module) : throw new FailedException("Module [$name] not Found");
    }

    public function update(string $name, array $module): bool
    {
        File::put(
            $this->moduleJson,
            $this->all()->map(function ($m) use ($module, $name)
            {
                if (Str::of($name)->exactly($m['name'])) {
                    return array_merge($m, $module);
                }
                $this->removeDirs($m);
                return $m;
            })->values()->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
        return true;
    }

    public function delete(string $name): bool
    {
        File::put(
            $this->moduleJson,
            $this->all()->filter(fn($module) => !Str::of($name)->exactly($module['name']))->toJson(
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            )
        );
        return true;
    }

    public function disOrEnable($name): bool|int
    {
        return File::put(
            $this->moduleJson,
            $this->all()->map(function ($module) use ($name)
            {
                if (Str::of($module['name'])->exactly($name)) {
                    $module['enable'] = !$module['enable'];
                }
                return $module;
            })->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    public function getEnabled(): Collection
    {
        return $this->all()->where('enable', true)->values();
    }

    public function enabled(string $moduleName): bool
    {
        return $this->getEnabled()->pluck('name')->contains($moduleName);
    }

    protected function hasSameModule(array $module, Collection $modules): void
    {
        if ($modules->pluck('name')->contains($module['name'])) {
            throw new FailedException(sprintf('Module [%s] has been created', $module['name']));
        }
    }

    protected function removeDirs(array &$modules): void
    {
        unset($modules['dirs']);
    }
}
