<?php

namespace Xditn\Base\modules\Pay\Support;

interface PayInterface
{
    public function pay(array $params): array;

    public function notify(array $params): array;
}
