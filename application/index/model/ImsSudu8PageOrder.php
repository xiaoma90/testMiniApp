<?php
namespace app\index\model;
use think\Model;
use think\Db;
use app\index\model\ImsSudu8PageDuoProductsAddress as address;

class ImsSudu8PageOrder extends Model{
    protected $pk = 'id';
    protected $uniacid = '';
    protected $name = 'wd_xcx_order';

    //初始化
    protected function initialize()
    {
        $this ->uniacid = input('appletid');
    }

    protected function getAddress($search_type, $search_keys){
        $id = [];
        $add = new address();
        $res = $add ->where(function($query)use($search_type, $search_keys){
            if ($search_type == 2) {
                $query ->where('name', 'like', '%' . $search_keys . '%');
            }
            if ($search_type == 3) {
                $query ->where('mobile', 'like', '%' . $search_keys . '%');
            }
            if ($search_type == 4) {
                $query ->where('address', 'like', '%' . $search_keys . '%');
            }
        }) ->field('id')
            ->select();
        foreach ($res as $value) {
            array_push($id, $value->id);
        }
        return $id;
    }


    protected function getNavAddress($search_type, $search_keys){
        $id = [];
        $res = $this->where('uniacid', $this->uniacid)
                    ->where('is_more', 0)
                    ->where('nav', 1)
                    ->field('id, m_address')
                    ->select();
        foreach ($res as $key => $value) {
            $m_address = unserialize($value['m_address']);
            if ($search_type == 2) {
                if(strpos($m_address['name'], $search_keys) !== false){
                    array_push($id, $value['id']);
                }
            }
            if ($search_type == 3) {
                if(strpos($m_address['mobile'], $search_keys) !== false){
                    array_push($id, $value['id']);
                }
            }
            if ($search_type == 4) {
                if(strpos($m_address['address'], $search_keys) !== false){
                    array_push($id, $value['id']);
                }
            }
        }

        return $id;

    }


    //获取订单列表
    public function getOrders($where, $search_flag, $search_type, $search_keys, $start_get, $end_get){
        $ids = $this->getNavAddress($search_type, $search_keys);
        return $this->with('addressData')
                    ->where($where)
                    ->where(function($query)use($search_type, $search_keys, $ids){
                        if($search_type && $search_keys){
                            if($search_type != 1){
                                $query ->where('id', 'in', $ids);
                            }
                        }
                    })
                    ->where('uniacid', $this->uniacid)
                    ->where('is_more', 0)
                    ->order('id desc')
                    ->paginate(10,false,[ 'query' => array('appletid'=>$this->uniacid,'search_flag'=>$search_flag,"search_type"=>$search_type,"search_keys"=>$search_keys,"start_get"=>$start_get,"end_get"=>$end_get)]);
    }
    


    public function getReserveOrders($where, $order, $end_datetimepicker2, $end_datetimepicker, $select_state, $datetimepicker,$datetimepicker3){
        return $this->where($where)
                    ->where('uniacid', $this->uniacid)
                    ->where("is_more",1)
                    ->order('id desc')
                    ->paginate(10,false,[ 'query' => array('appletid'=>$this->uniacid,'order'=>$order,'end_datetimepicker2'=>$end_datetimepicker2,'end_datetimepicker'=>$end_datetimepicker,'select_state'=>$select_state,'datetimepicker'=>$datetimepicker,'datetimepicker3'=>$datetimepicker3)]);
    }

    //地址表一对一关联
    public function addressData(){
        return $this->hasOne('ImsSudu8PageDuoProductsAddress','id', 'address');
    }






}