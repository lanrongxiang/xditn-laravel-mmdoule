<?php

namespace Xditn\Base\modules\Shop\Services\Product;

interface ProductInterface
{
    public function store(array $data);

    public function show($id);

    public function update($id, array $data);

    public function destroy($id);
}
