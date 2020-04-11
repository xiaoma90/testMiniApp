<?php
namespace app\index\model;
use think\Model;
use think\Db;

class ImsSudu8PageFxLs extends Model
{
    protected $pk = 'id';
    protected $uniacid = '';
    protected $name = 'wd_xcx_fx_ls';

    //初始化
    protected function initialize()
    {
        $this->uniacid = input('appletid');
    }

    //是否存在分销订单
    public function isFxOrder($order_id){
        $count = $this->where('order_id', $order_id) ->where('uniacid', $this->uniacid) ->count();
        return $count;
    }

}