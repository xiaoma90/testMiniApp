<?php
namespace app\index\model;
use think\Model;
use think\Db;


class ImsSudu8PageExternalOrder extends Model
{
    protected $pk = 'id';
    protected $uniacid = '';
    protected $name = 'wd_xcx_external_order';

    protected function initialize()
    {
        $this->uniacid = input('appletid');
    }

    public function getAllGoods(){
        return $this->where('uniacid', $this->uniacid)
            ->order('order_create_time desc')
            ->paginate(10,false,[ 'query' => array('appletid'=>$this->uniacid)]);
    }

    /*搜索商品*/
    public function search($where){
        return $this->where('uniacid', $this->uniacid)
            ->where($where)
            ->order('id desc')
            ->paginate(10,false,[ 'query' => array('appletid'=>$this->uniacid)]);
    }

    //搜索未完成的订单
    public function getJdOrders(){
        return $this ->where('order_status', 'not in', '4,18') ->where('type', 'jd') ->select();
    }

}