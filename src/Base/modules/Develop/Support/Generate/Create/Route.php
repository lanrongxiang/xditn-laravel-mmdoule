<?php

declare(strict_types=1);

namespace Xditn\Base\modules\Develop\Support\Generate\Create;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Xditn\MModule;

/**
 * Route
 */
class Route extends Creator
{
    public function __construct(public readonly string $controller)
    {
    }

    /**
     * get file
     */
    public function getFile(): string
    {
        return MModule::getModuleRoutePath($this->module);
    }

    /**
     * get content
     */
    public function getContent(): string
    {
        // route 主要添加两个点
        // use Controller
        // 添加路由
        $route = Str::of('');

        $originContent = $this->getOriginContent();

        // 如果已经有 controller，就不再追加路由
        if (Str::of($originContent)->contains($this->getUserController())) {
            return $originContent;
        }

        File::lines(MModule::getModuleRoutePath($this->module))
            ->each(function ($line) use (&$route) {
                if (Str::of($line)->contains('Route::prefix')) {
                    $route = $route->trim(PHP_EOL)
                        ->newLine()
                        ->append($this->getUserController())
                        ->append(';')
                        ->newLine(2)
                        ->append($line)
                        ->newLine();
                } else {
                    $route = $route->append($line)->newLine();
                }
            });

        $apiResource = "Route::apiResource('{api}', {controller}::class);";

        return Str::of($route->toString())->replace(
            ['{module}', '//next'],
            [
                lcfirst($this->module),
                Str::of($apiResource)->replace(['{api}', '{controller}'], [$this->getApiString(), $this->getControllerName()])
                    ->prepend("\t")
                    ->prepend(PHP_EOL)
                    ->newLine()->append("\t//next"), ]
        )->toString();
    }

    /**
     * @return string
     */
    public function getOriginContent(): string
    {
        return File::get(MModule::getModuleRoutePath($this->module));
    }

    /**
     * @param  string  $content
     * @return bool|int
     */
    public function putOriginContent(string $content): bool|int
    {
        return File::put(MModule::getModuleRoutePath($this->module), $content);
    }

    /**
     * get api
     */
    public function getApiString(): string
    {
        return Str::of($this->getControllerName())->remove('Controller')->snake('_')->replace('_', '/')->toString();
    }

    /**
     * get api route
     */
    public function getApiRoute(): string
    {
        return lcfirst($this->module).'/'.$this->getApiString();
    }

    /**
     * use controller
     */
    protected function getUserController(): string
    {
        return 'use '.MModule::getModuleControllerNamespace($this->module).$this->getControllerName();
    }

    /**
     * get controller name
     */
    protected function getControllerName(): string
    {
        return Str::of($this->controller)->whenContains('Controller', function ($value) {
            return Str::of($value)->ucfirst();
        }, function ($value) {
            return Str::of($value)->append('Controller')->ucfirst();
        })->toString();
    }
}
