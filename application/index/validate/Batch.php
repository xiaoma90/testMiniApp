<?php

namespace app\common\validate;


use app\index\model\QrCodeModel;
use think\Db;
use think\Validate;

class Batch extends Validate
{
    protected $rule = [
        'codes' => 'require',
        'product_id' => 'require|gt:0|checkProduct',
        'shop_id' => 'checkShop',
        'produce_time' => 'date',
    ];
    protected $message = [
        'codes.require' => '请录入至少一组code码',
        'product_id.require' => '请选择绑定商品',
        'product_id.gt' => '请选择绑定商品',
        'produce_time.date' => '请输入正确的日期格式',
    ];

    //检测商品是否存在
    protected function checkProduct($value)
    {
        $product = Db::name('wd_xcx_products')
            ->where([
                'type' => 'showProMore',
                'is_sale' => 0
            ])
            ->find($value);

        if (!$product) {
            return '该商品不存在！';
        } else {
            return true;
        }
    }

    //检测经销商是否存在
    protected function checkShop($value)
    {
        if ($value) {
            // wd_xcx_shops_shop flag 1上架  status 1已审核
            $shop = Db::name('wd_xcx_shops_shop')
                ->where([
                    'flag' => 1,
                    'status' => 1
                ])
                ->find($value);
            if (!$shop) {
                return '该商户不存在！';
            }
        }
        return true;

    }

}