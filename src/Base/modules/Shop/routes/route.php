<?php

use Illuminate\Support\Facades\Route;
use Xditn\Base\modules\Shop\Http\Controllers\CategoryController;
use Xditn\Base\modules\Shop\Http\Controllers\ConfigController;
use Xditn\Base\modules\Shop\Http\Controllers\CouponController;
use Xditn\Base\modules\Shop\Http\Controllers\DiyTemplatesController;
use Xditn\Base\modules\Shop\Http\Controllers\PointController;
use Xditn\Base\modules\Shop\Http\Controllers\ProductBrandController;
use Xditn\Base\modules\Shop\Http\Controllers\ProductController;
use Xditn\Base\modules\Shop\Http\Controllers\ProductServicesController;
use Xditn\Base\modules\Shop\Http\Controllers\ProductSpecTmpController;
use Xditn\Base\modules\Shop\Http\Controllers\ProductTagsController;
use Xditn\Base\modules\Shop\Http\Controllers\SeckillActivityController;
use Xditn\Base\modules\Shop\Http\Controllers\SeckillProductsController;
use Xditn\Base\modules\Shop\Http\Controllers\ShipTemplateController;
use Xditn\Base\modules\Shop\Http\Controllers\VipRechargePlansController;

Route::prefix('shop')->group(function () {

    Route::apiResource('category', CategoryController::class)->names('shop_category');
    Route::put('category/enable/{id}', [CategoryController::class, 'enable'])->name('shop_category.enable');

    Route::apiResource('products', ProductController::class);
    Route::put('products/enable/{id}', [ProductController::class, 'enable']);
    Route::put('products/shelf/{id}', [ProductController::class, 'shelf']);
    Route::put('products/unshelf/{id}', [ProductController::class, 'unshelf']);
    Route::put('products/delist/{id}', [ProductController::class, 'delist']);

    Route::apiResource('product/spec/tmp', ProductSpecTmpController::class);

    Route::apiResource('product/tags', ProductTagsController::class);

    Route::apiResource('product/brand', ProductBrandController::class);

    Route::apiResource('product/services', ProductServicesController::class);

    Route::apiResource('ship/template', ShipTemplateController::class)->names('shop_template');

    Route::apiResource('diy/templates', DiyTemplatesController::class);
    Route::get('diy/category/products', [DiyTemplatesController::class, 'getProducts']);
    // 优惠券
    Route::apiResource('coupon', CouponController::class)->names('shop_coupon');
    Route::put('coupon/enable/{id}', [CouponController::class, 'enable']); // 优惠券启用
    Route::get('coupon/receive/records', [CouponController::class, 'records']); // 优惠券领取记录
    Route::post('coupon/give/{id}', [CouponController::class, 'give']); // 发放优惠券
    Route::get('point', [PointController::class, 'index'])->name('point.index'); // 积分明细
    Route::match(['get', 'post'], 'point/setting', [PointController::class, 'setting'])->name('point.setting'); // 积分设置
    Route::match(['get', 'post'], 'config/free/shipping', [ConfigController::class, 'freeShipping'])->name('config.freeShipping'); // 包邮设置
    Route::match(['get', 'post'], 'config/vip/recharge', [ConfigController::class, 'vipRecharge'])->name('config.vipRecharge'); // 会员充值设置
    Route::match(['get', 'post'], 'config/logistics', [ConfigController::class, 'logistics'])->name('config.logistics'); // 物流配置
    // 会员充值套餐
    Route::apiResource('vip/recharge/plans', VipRechargePlansController::class)->names('shop_vip_recharge_plans');
    // 秒杀商品
    Route::apiResource('seckill/products', SeckillProductsController::class)->names('shop_seckill_products');
    Route::put('seckill/products/enable/{id}', [SeckillProductsController::class, 'enable'])->name('shop_seckill_products.enable');
    // 秒杀活动
    Route::apiResource('seckill/activity', SeckillActivityController::class)->names('shop_seckill_activity');
    Route::put('seckill/activity/enable/{id}', [SeckillActivityController::class, 'enable'])->name('shop_seckill_activity.enable');
    //next
});
