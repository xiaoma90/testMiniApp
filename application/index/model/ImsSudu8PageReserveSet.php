<?php
namespace app\index\model;
use think\Model;
use think\Db;

class ImsSudu8PageReserveSet extends Model
{
    protected $pk = 'id';
    protected $uniacid = '';
    protected $name = 'wd_xcx_reserve_set';

    //初始化
    protected function initialize()
    {
        $this->uniacid = input('appletid');
    }

    //获取当前项目预约预定商品基础设置
    public function getBaseSet(){
        return $this->where('uniacid', $this->uniacid) ->find();
    }
}