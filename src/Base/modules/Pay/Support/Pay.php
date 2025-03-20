<?php

namespace Xditn\Base\modules\Pay\Support;

abstract class Pay implements PayInterface
{
    /**
     * 支付实例
     *
     * @return mixed
     */
    abstract protected function instance(): mixed;

    /**
     * @param $name
     * @param $params
     * @return mixed
     */
    public function __call($name, $params)
    {
        return $this->instance()->{$name}(...$params);
    }
}
