<?php

namespace app\index\model;

use think\Db;
use think\Model;

/**
 * @property mixed id
 */
class QrBatchModel extends Model
{
    const STATUS_OK = 1;

    protected $name = 'wd_xcx_qr_batch';

    protected $dateFormat = "Y-m-d H:i";

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    public function getProduceTimeAttr($time)
    {
        return date('Y-m-d H:i', $time);
    }

    //首页列表
    public function index($data)
    {
        $filter = [];
        if (!empty($data['product_name'])) {
            $filter['b.title'] = ['like', '%' . $data['product_name'] . '%'];
        }
        if (!empty($data['shop_name'])) {
            $filter['c.name'] = ['like', '%' . $data['shop_name'] . '%'];
        }

        return (sizeof($filter) ? self::where($filter) : $this)
            ->alias('a')
            ->field('a.id, a.create_time, a.produce_time, a.qr_num, b.id as pro_id, b.title as pro_name, c.id as shop_id, c.name as shop_name')
            ->join('wd_xcx_products b', 'a.product_id = b.id')
            ->join('wd_xcx_shops_shop c', 'a.shop_id = c.id', 'LEFT')
            ->order(['a.create_time desc'])
            ->paginate($data['size'], false, [
                'query' => $data
            ]);
    }


    /**
     * 新增批次
     * @param $data
     * @return bool
     */
    public function binding($data)
    {
        $codes = $data['codes'];
        $data['produce_time'] = strtotime($data['produce_time']);
        $data['uniacid'] = $data['appletid'];
        $data['qr_num'] = sizeof($codes);
        unset($data['codes']);

        $this->allowField(true)->save($data);
        foreach ($codes as $code) {
            QrCodeModel::where('code', trim($code))
                ->update([
                    'is_binding' => self::STATUS_OK,
                    'binding_time' => time(),
                    'batch_id' => $this->id,
                ]);
        }
    }


    /*
     * 以下查询暂存此处
     * 获取可以绑定批次的商品
     */
    public function productsCanBind($appletId)
    {

        return Db::name('wd_xcx_products')
            ->alias('a')
            ->join('wd_xcx_cate_pro b', 'a.id = b.pid')
            ->join('wd_xcx_cate c', 'b.cate_id = c.id')
            ->where([
                'a.type' => 'showProMore',
                'a.is_sale ' => 0,
                'a.uniacid' => $appletId,
                'c.statue' => self::STATUS_OK,
            ])
            ->field(['a.id', 'a.title'])
            ->group('a.id')
            ->select();
    }


    //获取产品的多规格数据
    public function productMoreValue($product_id)
    {
        $productTypeValues = Db::name('wd_xcx_duo_products_type_value')->where('pid', $product_id)->order('id asc')->select();

        $moreValue = [];
        if (sizeof($productTypeValues)) {
            $types = explode(",", $productTypeValues[0]['comment']);
                foreach ($types as $typeKey => $type) {
                    $moreValue[$typeKey]['key'] = $type;
                    $moreValue[$typeKey]['value'] = [];
                    foreach ($productTypeValues as $key => $value) {
                        if (!in_array($value['type' . ($typeKey + 1)], $moreValue[$typeKey]['value'])) {
                            $moreValue[$typeKey]['value'][]=$value['type' . ($typeKey + 1)];
                        }
                    }
                }
            }
        return $moreValue;
    }

    public function shopsCanBind($appletId)
    {
        return Db::name('wd_xcx_shops_shop')
            ->where([
                'flag' => self::STATUS_OK,
                'status' => self::STATUS_OK,
                'uniacid' => $appletId,
            ])
            ->field(['id', 'name'])
            ->select();
    }

}
