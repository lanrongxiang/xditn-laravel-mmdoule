<?php

namespace Xditn\Support;

use Illuminate\Routing\ResourceRegistrar as LaravelResourceRegistrar;
use Illuminate\Routing\Route;

class ResourceRegistrar extends LaravelResourceRegistrar
{
    protected $resourceDefaults = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy', 'enable', 'export'];

    protected function addResourceEnable($name, $base, $controller, $options): Route
    {
        $name = $this->getShallowName($name, $options);

        $uri = $this->getResourceUri($name).'/enable/{'.$base.'}';

        $action = $this->getResourceAction($name, $controller, 'enable', $options);

        return $this->router->put($uri, $action);
    }

    protected function addResourceExport($name, $base, $controller, $options): Route
    {
        $uri = $this->getResourceUri($name).'/export';

        unset($options['missing']);

        $action = $this->getResourceAction($name, $controller, 'export', $options);

        return $this->router->get($uri, $action);
    }
}
