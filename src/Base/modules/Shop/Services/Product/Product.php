<?php

namespace Xditn\Base\modules\Shop\Services\Product;

use Exception;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Pipeline;
use Throwable;
use Xditn\Base\modules\Shop\Models\Pivots\ProductHasSpecs;
use Xditn\Base\modules\Shop\Models\ProductInfo;
use Xditn\Base\modules\Shop\Models\Products;
use Xditn\Base\modules\Shop\Models\ProductSku;
use Xditn\Base\modules\Shop\Models\ProductSpec;
use Xditn\Base\modules\Shop\Models\ProductSpecValues;
use Xditn\Exceptions\FailedException;

class Product implements ProductInterface
{
    protected array $pipes = [];

    public function __construct(
        protected Products $productsModel,
        protected ProductSpec $productSpecModel,
        protected ProductSpecValues $productSpecValuesModel,
        protected ProductSku $productSkuModel,
        protected ProductInfo $productInfoModel
    ) {

    }

    /**
     * 保存信息
     *
     * @param array{
     *     basic:array{type: int,title: string,keywords: string,
     *      subtitle: string,images: array,video: string,brand_id: int,
     *      category_ids: array,service_ids: array,
     *      tag_ids:array,unit: string,virtual_sales: number,sort: number,
     *      is_available: number,is_schedule: number,
     *      schedule_time: number},
     *     specifications:array{is_specifications:int,
     *      sku:array{alert_stock:int,cost_price:int,list_price:int,
     *      price:int,product_no:string,stock:int,volume:int,weight:int},
     *      skus:array{array{alert_stock:int,cost_price:int,list_price:int,
     *       price:int,product_no:string,stock:int,volume:int,weight:int}}
     *      },
     *     info:array{content:string},
     *     ship:array{ship_fee:int,ship_type:int,ship_template_id:int},
     *     config:array{is_show_cost_price :int, is_show_list_price:int, is_show_virtual_sales:int,params:array{key:string,value:string}}
     * } $data
     */
    public function store(array $data): mixed
    {
        return DB::transaction(function () use ($data) {
            // 过滤信息
            $data = Pipeline::send($data)->through($this->pipes)->then(fn ($data) => $data);
            // 保存商品
            $this->productsModel->storeBy($data['basic']);
            // 保存分类关联
            $categoryIds = $data['basic']['category_ids'];
            $this->productsModel->categories()->attach($categoryIds);
            // 保存服务关联
            $serviceIds = $data['basic']['service_ids'];
            $this->productsModel->services()->attach($serviceIds);
            // 保存标签关联
            $tagIds = $data['basic']['tag_ids'];
            $this->productsModel->tags()->attach($tagIds);
            // 保存商品额外信息
            $this->productsModel->info()->create($data['info']);
            // 保存商品 SKU 信息
            if ($this->productsModel->isMultiSpecification()) {
                $specifications = $data['specifications'];
                // 规格
                $specIds = $specValueIds = [];
                // 处理规格信息
                foreach ($specifications['specs'] as $specName => $specValues) {
                    $specId = $this->productSpecModel->where('name', $specName)->value('id');
                    if (! $specId) {
                        $specId = $this->productSpecModel->createBy([
                            'name' => $specName,
                        ]);
                    }
                    // 保存规格名称ID映射
                    $specIds[$specName]['id'] = $specId;
                    // 保存规格值
                    foreach ($specValues as $value) {
                        $valueId = $this->productSpecValuesModel->where('spec_id', $specId)->where('value', $value)->value('id');
                        if (! $valueId) {
                            $valueId = $this->productSpecValuesModel->createBy([
                                'value' => $value,
                                'spec_id' => $specId,
                            ]);
                        }
                        // 保存规格值和ID映射
                        $specValueIds[$value] = $valueId;
                        // 保存规格对应的 value IDs
                        $specIds[$specName]['values'][] = $valueId;
                    }
                }

                // 保存商品和规格&规格值的关联关系
                $productSpecs = [];
                foreach ($specIds as $spec) {
                    foreach ($spec['values'] as $v) {
                        $productSpecs[] = [
                            'spec_id' => $spec['id'],
                            'spec_value_id' => $v,
                            'product_id' => $this->productsModel->id,
                        ];
                    }
                }
                ProductHasSpecs::insert($productSpecs);
                // 处理 SKUs
                $specifications['skus'] = $this->dealWithSkus($specifications['skus'], $specValueIds);
                try {
                    $this->productSkuModel->insert($specifications['skus']);
                } catch (Exception|Throwable $e) {
                    if ($e instanceof UniqueConstraintViolationException) {
                        preg_match('/\'(.*?)\'/', $e->errorInfo[2], $m);
                        throw new FailedException('商品的 SKU 编码有重复，请检查。'.(count($m) ? "编码号是【$m[1]】" : ''));
                    }
                }
            }
        });
    }

    public function show($id)
    {
        /* @var Products $product */
        $product = $this->productsModel->where('id', $id)
            ->with([
                'tags',
                'categories',
                'services',
                'info',
                'skus',
                'specifications',
                'specificationValues',
            ])
            ->first();

        $product->category_ids = $product->categories->pluck('id');
        $product->service_ids = $product->services->pluck('id');
        $product->tag_ids = $product->tags->pluck('id');

        // 删除相关关联
        unset($product['tags'], $product['categories'], $product['services']);

        // 获取 info 信息
        $info = $product['info'];
        unset($product['info']);

        // 规格处理
        $sku = $skus = $specs = [];
        if ($product->isSingleSpecification()) {
            $sku = [
                'stock' => $product->stock,
                'alert_stock' => $product->alert_stock,
                'price' => $product->price,
                'product_no' => $product->product_no,
                'weight' => $product->weight,
                'unit' => $product->unit,
                'volume' => $product->volume,
                'cost_price' => $product->cost_price,
                'list_price' => $product->list_price,
            ];
        } else {
            // 处理多规格
            $specificationValues = [];
            $product->specifications->each(function ($specification) use (&$specificationValues, &$specs, $product) {
                if (! isset($specs[$specification->id])) {
                    $specs[$specification->id] = [
                        'id' => $specification->id,
                        'name' => $specification->name,
                        'values' => [],
                    ];
                }

                $product->specificationValues->each(function ($value) use (&$specificationValues, &$specs, $specification) {
                    if ($specification->id == $value->spec_id) {
                        $specificationValues[$value->value] = $specification->name;

                        $specs[$specification->id]['values'][] = [
                            'id' => $value->id,
                            'value' => $value->value,
                        ];
                    }
                });
            });

            // 处理成给前端使用的规格数据结构
            $specs = array_values($specs);
            foreach ($specs as $k => &$spec) {
                $spec['key'] = $k;
                foreach ($spec['values'] as $key => &$value) {
                    $value['key'] = $key;
                }
            }

            // 处理 sku 参数
            $product->skus->each(function (&$sku) use ($specificationValues) {
                $specValues = json_decode($sku->spec_values, true);
                foreach ($specValues as &$specValue) {
                    if (isset($specificationValues[$specValue['value']])) {
                        $sku['s_'.$specificationValues[$specValue['value']]] = $specValue['value'];

                        // 这里加入 value 对应的规格名称
                        $specValue['spec_name'] = $specificationValues[$specValue['value']];
                    }
                }
            });

            // 删除冗余信息
            $skus = $product->skus;
            foreach ($skus as &$sku) {
                unset($sku['spec_values'], $sku['spec_value_ids']);
            }
            unset($product->specifications, $product->specificationValues, $product->skus);
        }

        return [
            'basic' => $product,
            'info' => $info,
            'ship' => [
                'ship_type' => $product['ship_type'],
                'ship_fee' => $product['ship_fee'],
                'ship_template_id' => $product['ship_template_id'],
            ],
            'specifications' => [
                'is_specifications' => $product['is_specifications'],
                'sku' => $sku,
                'skus' => $skus,
                'specs' => $specs,
            ],
        ];
    }

    public function update($id, array $data)
    {
        // TODO: Implement update() method.
        return DB::transaction(function () use ($id, $data) {
            // 过滤信息
            $data = Pipeline::send($data)->through($this->pipes)->then(fn ($data) => $data);
            /** @var Products $product */
            $product = $this->productsModel->with(['specifications', 'specificationValues'])->find($id);
            if (! $product) {
                throw new FailedException('商品不存在');
            }

            // 更新商品基本信息
            unset($data['basic']['id']);
            $this->productsModel->updateBy($id, $data['basic']);
            // 更新分类关联
            $categoryIds = $data['basic']['category_ids'];
            $product->categories()->sync($categoryIds);
            // 更新服务关联
            $serviceIds = $data['basic']['service_ids'];
            $product->services()->sync($serviceIds);
            // 更新标签关联
            $tagIds = $data['basic']['tag_ids'];
            $product->tags()->sync($tagIds);
            // 更新商品额外信息
            $infoId = $data['info']['id'];
            unset($data['info']['id']);
            $this->productInfoModel->where('id', $infoId)->update($data['info']);

            // 保存商品 SKU 信息
            if ($product->isMultiSpecification()) {
                $specifications = $data['specifications'];
                // 更新的规格信息存在 extra 数组中
                $specs = $specifications['specs'];
                // 规格
                $specIds = $specValueIds = [];
                // 组织原始规格，防止重复更新
                $originSpecs = $product->specifications->keyBy('id')->toArray();
                $originTmpSpecValues = $product->specificationValues->groupBy('spec_id');
                foreach ($originSpecs as $specId => &$spec) {
                    if (isset($originTmpSpecValues[$specId])) {
                        $spec['values'] = $originTmpSpecValues[$specId]->keyBy('id')->toArray();
                    }
                }
                // 删除 loop 变量
                unset($spec);
                // 更新规格
                foreach ($specs as $spec) {
                    // 首先处理规格相关
                    $specId = $spec['id'] ?? null;
                    if (! $specId) {
                        $specId = $this->productSpecModel->createBy(['name' => $spec['name']]);
                    } else {
                        $originSpecName = $originSpecs[$specId]['name'] ?? null;
                        // 如果原始规格名称不同，则更新规格名称
                        if ($originSpecName != $spec['name']) {
                            $this->productSpecModel->where('id', $id)->update(['name' => $spec['name']]);
                        }
                    }

                    // 保存规格名称ID映射
                    $specIds[$spec['name']]['id'] = $specId;

                    // 处理规格值
                    $originSpecValues = $originSpecs[$specId]['values'] ?? [];
                    foreach ($spec['values'] as $specValue) {
                        $valueId = $specValue['id'] ?? null;
                        if (! $valueId) {
                            $valueId = $this->productSpecValuesModel->createBy([
                                'spec_id' => $specId,
                                'value' => $specValue['value'],
                            ]);
                        } else {
                            $originSpecValue = $originSpecValues[$valueId]['value'] ?? null;
                            // 如果原始规格值不同，则更新规格值
                            if ($originSpecValue != $specValue['value']) {
                                $this->productSpecValuesModel->where('id', $valueId)->update(['value' => $specValue['value']]);
                            }
                        }
                        // 保存规格值和ID映射
                        $specValueIds[$specValue['value']] = $valueId;
                        // 保存规格对应的 value IDs
                        $specIds[$spec['name']]['values'][] = $valueId;
                    }
                }

                // 保存商品和规格&规格值的关联关系
                $productSpecs = [];
                foreach ($specIds as $spec) {
                    foreach ($spec['values'] as $v) {
                        $productSpecs[] = [
                            'spec_id' => $spec['id'],
                            'spec_value_id' => $v,
                            'product_id' => $product->id,
                        ];
                    }
                }
                $product->specificationValues()->detach();
                ProductHasSpecs::insert($productSpecs);

                // 处理 SKUs
                $specifications['skus'] = $this->dealWithSkus($specifications['skus'], $specValueIds);
                $originSkus = [];
                // 使用 value ID 拼接作为数组的唯一标识
                $product->skus()->get()->each(function ($item) use (&$originSkus) {
                    $originSkus[implode('_', json_decode($item['spec_value_ids']))] = $item->toArray();
                });

                // 保留原始 SKU IDs
                $updateSkus = $newSkus = [];
                foreach ($specifications['skus'] as $sku) {
                    $valueKey = implode('_', json_decode($sku['spec_value_ids']));
                    // 如果原有的 KEY，则保存
                    if (isset($originSkus[$valueKey])) {
                        $sku['id'] = $originSkus[$valueKey]['id'];
                        $sku['updated_at'] = time();
                        $sku['product_id'] = $product->id;
                        $updateSkus[] = $sku;
                    } else {
                        $newSkus[] = $sku;
                    }
                }
                // 待删除的 SKU IDs
                $deletingSkuIds = array_values(array_diff(array_column($originSkus, 'id'), array_column($updateSkus, 'id')));

                foreach ($updateSkus as $updateSku) {
                    $id = $updateSku['id'];
                    unset($updateSku['id']);
                    ProductSku::where('id', $id)->update($updateSku);
                }

                if (count($deletingSkuIds)) {
                    ProductSku::whereIn('id', $deletingSkuIds)->delete();
                }

                // ProductNo 使用 unique 特性去重
                try {
                    if (count($newSkus)) {
                        foreach ($newSkus as &$newSku) {
                            $newSku['product_id'] = $product->id;
                        }
                        $this->productSkuModel->insert($newSkus);
                    }
                } catch (Exception|Throwable $e) {
                    if ($e instanceof UniqueConstraintViolationException) {
                        preg_match('/\'(.*?)\'/', $e->errorInfo[2], $m);
                        throw new FailedException('商品的 SKU 编码有重复，请检查。'.(count($m) ? "编码号是【$m[1]】" : ''));
                    }

                    throw new FailedException($e->getMessage());
                }
            }
        });
    }

    /**
     * 处理 skus
     */
    protected function dealWithSkus(array $skus, array $specValueIds): array
    {
        foreach ($skus as &$sku) {
            $specValues = [];
            foreach ($sku['spec_values'] as $value) {
                $specValues[] = [
                    'id' => $specValueIds[$value],
                    'value' => $value,
                ];
            }
            $sku['spec_values'] = json_encode($specValues, JSON_UNESCAPED_UNICODE);
            $sku['spec_value_ids'] = json_encode(array_unique(array_column($specValues, 'id')));
            $sku['product_id'] = $this->productsModel->id;
            $sku['created_at'] = $sku['updated_at'] = time();
        }

        return $skus;
    }

    /**
     * 删除产品
     *
     * @return mixed
     */
    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            // TODO: Implement destroy() method.
            /** @var Products $product */
            $product = $this->productsModel->firstBy($id);
            if ($product) {
                // 删除分类
                $product->categories()->detach();
                // 删除 info
                $product->info()->delete();
                // 删除服务
                $product->services()->detach();
                // 删除标签
                $product->tags()->detach();
                // 删除 sku
                $product->skus()->delete();
                // 删除规格
                $product->specificationValues()->detach();

                // 最后删除产品
                return $product->delete();
            }
        });
    }

    public function enable($id, string $field): bool
    {
        return $this->productsModel->toggleBy($id, $field);
    }
}
