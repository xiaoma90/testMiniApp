<?php
namespace app\index\model;
use think\Model;
use think\Db;

class WdXcxMainShopOrder extends Model{
    protected $pk = 'id';

    //关联子订单
    public function orderItems(){
        return $this ->hasMany('app\index\model\WdXcxMainShopOrderItem', 'order_id', 'order_id');
    }

}