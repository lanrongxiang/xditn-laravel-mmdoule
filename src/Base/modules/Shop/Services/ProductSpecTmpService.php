<?php

namespace Xditn\Base\modules\Shop\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Xditn\Base\modules\Shop\Models\ProductSpec;
use Xditn\Base\modules\Shop\Models\ProductSpecTmp;
use Xditn\Base\modules\Shop\Models\ProductSpecValues;
use Xditn\Exceptions\FailedException;

/**
 * 产品规格服务
 */
class ProductSpecTmpService
{
    public function __construct(
        protected readonly ProductSpecTmp $productSpecTmp,
        protected readonly ProductSpec $productSpec,
        protected readonly ProductSpecValues $productSpecValues
    ) {
    }

    public function getList(): mixed
    {
        return $this->productSpecTmp->setBeforeGetList(function ($query) {
            return $query->with(['specs' => function ($query) {
                $query->with(['values']);
            }]);
        })->getList();
    }

    /**
     * 产品规格
     */
    public function store(array $params): mixed
    {
        return DB::transaction(function () use ($params) {
            $specs = $params['specs'];
            unset($params['specs']);

            if ($this->productSpecTmp->where('name', $params['name'])->first()) {
                throw new FailedException('规格名称已存在');
            }

            $specTmpId = $this->productSpecTmp->storeBy($params);
            $specs = $this->checkSpecs($specs);
            if ($specs->count()) {
                $specs->each(function ($spec) use ($specTmpId) {
                    $spec['spec_tmp_id'] = $specTmpId;

                    $specId = $this->productSpec->createBy($spec);

                    $values = $spec['values'];

                    $values = $values->each(function (&$value) {
                        unset($value['key']);
                    });

                    $this->productSpec->find($specId)->values()->createMany($values);
                });
            }

            return true;
        });
    }

    protected function checkSpecs(array $specs): Collection
    {
        // 去除规格名称为空的数据
        $specs = Collection::make($specs)->filter(function ($item) {
            return trim($item['name']);
        });

        if ($specs->count()) {
            // 判断规格名称是否重复
            $specNames = $specs->duplicates('name');
            if ($specNames->count()) {
                throw new FailedException('规格名称【'.$specNames->implode(',').'】重复');
            }

            // 判断规格值是否重复
            $specs = $specs->map(function ($spec) {
                // 去除规格值为空的数据
                $values = Collection::make($spec['values'])->filter(function ($item) {
                    return trim($item['value']);
                });

                if (! $values->count()) {
                    throw new FailedException(sprintf('规格【%s】的值不能为空', $spec['name']));
                }

                $specValues = $values->duplicates('value');
                if ($specValues->count()) {
                    throw new FailedException('规格【'.$spec['name'].'】的值【'.$specValues->implode(',').'】重复');
                }

                $spec['values'] = $values;

                return $spec;
            });
        }

        return $specs;
    }

    /**
     * first
     */
    public function first($id): mixed
    {
        $tmp = $this->productSpecTmp->with(['specs' => function ($query) {
            $query->with(['values']);
        }])->find($id);

        foreach ($tmp->specs as $key => &$spec) {
            $spec['key'] = $key;

            foreach ($spec['values'] as $key => &$value) {
                $value['key'] = $key;
            }
        }

        return $tmp;
    }

    public function update($id, array $params)
    {
        return DB::transaction(function () use ($id, $params) {
            if ($this->productSpecTmp
                ->where('name', $params['name'])
                ->where('id', '<>', $id)
                ->first()
            ) {
                throw new FailedException('规格名称已存在');
            }

            $this->productSpecTmp->updateBy($id, [
                'name' => $params['name'],
            ]);

            $specs = $this->checkSpecs($params['specs']);

            if ($specs->count()) {
                // 从最开始的地方获取获取删除的规格IDs，不然由于后面新增操作导致删除新的
                $deletedSpecIds = $this->productSpec->where('spec_tmp_id', $id)->pluck('id')->diffAssoc($specs->pluck('id'));

                $specs->each(function ($spec) use ($id) {
                    // 存在就更新
                    if (isset($spec['id'])) {
                        if ($this->productSpec->updateBy($spec['id'], [
                            'name' => $spec['name'],
                        ])) {
                            // 从最开始的地方获取删除的规格值的IDs，不然由于后面新增操作导致删除新的
                            $deletedSpecValIds = $this->productSpecValues->where('spec_id', $spec['id'])->pluck('id')->diffAssoc($spec['values']->pluck('id'));

                            foreach ($spec['values'] as $value) {
                                if (isset($value['id'])) {
                                    $this->productSpecValues->updateBy($value['id'], [
                                        'value' => $value['value'],
                                    ]);
                                } else {
                                    $this->productSpecValues->createBy([
                                        'spec_id' => $spec['id'],
                                        'value' => $value['value'],
                                    ]);
                                }
                            }

                            $this->productSpecValues->whereIn('id', $deletedSpecValIds)->delete();
                        }
                    } else {
                        // 新增规格
                        $specId = $this->productSpec->createBy([
                            'spec_tmp_id' => $id,
                            'name' => $spec['name'],
                        ]);

                        // 新增规格属性
                        if ($specId) {
                            foreach ($spec['values'] as $value) {
                                $this->productSpecValues->createBy([
                                    'spec_id' => $specId,
                                    'value' => $value['value'],
                                ]);
                            }
                        }
                    }
                });

                $this->productSpec->whereIn('id', $deletedSpecIds)->delete();
            }
        });
    }

    /**
     * 删除
     *
     * @return true
     */
    public function destroy($id): bool
    {
        return DB::transaction(function () use ($id) {
            $this->productSpecTmp->deleteBy($id);

            $specIds = $this->productSpec->where('spec_tmp_id', $id)->pluck('id');

            $this->productSpec->where('spec_tmp_id', $id)->delete();

            $this->productSpecValues->whereIn('spec_id', $specIds)->delete();

            return true;
        });
    }
}
