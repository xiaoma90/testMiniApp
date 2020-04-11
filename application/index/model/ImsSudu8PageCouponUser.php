<?php
namespace app\index\model;
use think\Model;
use think\Db;

class ImsSudu8PageCouponUser extends Model
{
    protected $pk = 'id';
    protected $uniacid = '';
    protected $name = 'wd_xcx_couponUser';

    //初始化
    protected function initialize()
    {
        $this->uniacid = input('appletid');
    }


    //关联优惠券
    public function couponInfo(){
        $this->haoOne('ImsSudu8PageCoupon', 'id', 'cid');
    }

    //获取优惠券信息
    public function getCou($id){
        return  $this ->with('couponInfo')
                    ->where('uniacid', $this->uniacid)
                    ->where('flag', 1)
                    ->get($id);
    }
}