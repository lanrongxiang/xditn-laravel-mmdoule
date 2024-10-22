<?php

namespace Xditn\Modules\Common\Http\Controllers;
use Exception;
use Xditn\Modules\Common\Repository\Options\Factory;

class OptionController
{
    /**
     * @param $name
     * @param Factory $factory
     * @return array
     * @throws Exception
     */
    public function index($name, Factory $factory): array
    {
        return $factory->make($name)->get();
    }
}
