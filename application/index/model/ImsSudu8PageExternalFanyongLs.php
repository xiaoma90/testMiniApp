<?php
namespace app\index\model;
use think\Model;
use think\Db;

class ImsSudu8PageExternalFanyongLs extends Model
{
    protected $pk = 'id';
    protected $uniacid = '';
    protected $name = 'wd_xcx_external_fanyong_ls';

    //初始化
    protected function initialize()
    {
        $this->uniacid = input('appletid');
    }

    //关联到订单表
    public function orderInfo(){
        return $this->hasOne('ImsSudu8PageExternalOrder', 'order_sn', 'order_sn');
    }

    //关联到用户表
    public function userInfo(){
        return $this->hasOne('ImsSudu8PageSuperuser', 'id', 'fxsid');
    }

    //获取所有的分佣流水
    public function getAllLs(){
        return $this->with('orderInfo')
                ->where('uniacid', $this->uniacid)
                ->order('id desc')
                ->paginate(10,false,[ 'query' => array('appletid'=>$this->uniacid)]);
    }

}