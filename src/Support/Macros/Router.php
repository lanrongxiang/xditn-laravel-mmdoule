<?php

declare(strict_types=1);

namespace Xditn\Support\Macros;

use Illuminate\Routing\Router as LaravelRouter;

class Router
{
    public function boot(): void
    {
        $this->adminResource();
    }

    protected function adminResource(): void
    {
        LaravelRouter::macro('adminResource', function ($name, $controller, array $options = []) {

            $only = ['index', 'show', 'store', 'update', 'destroy', 'enable', 'export'];

            if (isset($options['except'])) {
                $only = array_diff($only, (array) $options['except']);
            }

            return $this->resource($name, $controller, array_merge([
                'only' => $only,
            ], $options));
        });
    }
}
