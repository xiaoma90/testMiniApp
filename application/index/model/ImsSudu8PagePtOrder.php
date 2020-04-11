<?php
namespace app\index\model;
use think\Model;
use think\Db;


class ImsSudu8PagePtOrder extends Model{
    protected $pk = 'id';
    protected $uniacid = '';
    protected $name = 'wd_xcx_pt_order';

    protected function initialize()
    {
        $this ->uniacid = input('appletid');
    }

    //获取订单列表
    public function getOrders($where, $search_flag, $search_type, $search_keys, $start_get, $end_get){
        return $this->with('addressData')
                    ->where($where)
                    ->where('uniacid', $this->uniacid)
                    ->where('jqr', 1)
                    ->order('id desc')
                    ->paginate(10,false,[ 'query' => array('appletid'=>$this->uniacid,'search_flag'=>$search_flag,"search_type"=>$search_type,"search_keys"=>$search_keys,"start_get"=>$start_get,"end_get"=>$end_get)]);
    }

	//地址表一对一关联
    public function addressData(){
        return $this->hasOne('ImsSudu8PageDuoProductsAddress','id', 'address');
    }
}