<?php

namespace Xditn\Base\modules\Shop\Services;

use Illuminate\Support\Facades\Request;
use Xditn\Base\modules\Shop\Enums\ProductStatus;
use Xditn\Base\modules\Shop\Models\Pivots\CategoryHasProducts;
use Xditn\Base\modules\Shop\Models\Products;
use Xditn\Base\modules\Shop\Services\Product\ProductFactory;
use Xditn\Base\modules\Shop\Services\Product\ProductInterface;

/**
 * 产品服务
 */
class ProductService
{
    public function getList()
    {
        $productModel = new Products();

        if ($ids = Request::get('ids')) {
            $productModel = $productModel->hiddenPaginate();
        }

        return $productModel
            ->setBeforeGetList(function ($query) use ($ids) {
                if (Request::has('category_id')) {
                    return $query->whereIn(
                        'id',
                        CategoryHasProducts::where('category_id', Request::get('category_id'))->pluck('product_id')
                    );
                }

                if ($ids) {
                    return $query->whereIn('id', explode(',', $ids));
                }

                return $query;
            })

            ->getList();

    }

    /**
     * 新增产品
     */
    public function store(array $data): mixed
    {
        return $this->getProductService($data['basic']['type'])->store($data);
    }

    /**
     * 商品展示
     */
    public function show($id): mixed
    {
        return $this->getProductService(
            Products::where('id', $id)->value('type')
        )->show($id);
    }

    /**
     * 更新
     */
    public function update($id, array $data): mixed
    {
        return $this->getProductService($data['basic']['type'])->update($id, $data);
    }

    /**
     * 删除
     */
    public function destroy($id): bool
    {
        $ids = explode(',', $id);
        foreach ($ids as $id) {
            $type = Products::where('id', $id)->value('type');
            if ($type) {
                return $this->getProductService($type)->destroy($id);
            }
        }

        return true;
    }

    public function enable($id, string $field): bool
    {
        return $this->getProductService(
            Products::where('id', $id)->value('type')
        )->enable($id, $field);
    }

    public function shelf($id)
    {
        return Products::whereIn('id', explode(',', $id))->update(['is_available' => ProductStatus::AVAILABLE->value()]);

    }

    public function unshelf($id)
    {
        return Products::whereIn('id', explode(',', $id))->update(['is_available' => ProductStatus::NO_AVAILABLE->value()]);
    }

    public function delist($id)
    {
        return Products::whereIn('id', explode(',', $id))->update(['is_available' => ProductStatus::DELIST->value()]);
    }

    /**
     * 获取产品服务
     */
    protected function getProductService($type): ProductInterface
    {
        return app(ProductFactory::make($type));
    }
}
