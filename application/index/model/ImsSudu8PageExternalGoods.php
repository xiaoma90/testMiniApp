<?php
namespace app\index\model;
use think\Model;
use think\Db;


class ImsSudu8PageExternalGoods extends Model
{
    protected $pk = 'id';
    protected $uniacid = '';
    protected $name = 'wd_xcx_external_goods';

    protected function initialize()
    {
        $this->uniacid = input('appletid');
    }

    /*获取所有商品，带分页*/
    public function getAllGoods(){
        return $this->where('uniacid', $this->uniacid)
                    ->order('id desc')
                    ->paginate(10,false,[ 'query' => array('appletid'=>$this->uniacid)]);
    }



    /*按类型获取所有商品的商品ID*/
    public function getGoodIds($uniacid, $type){
        if($uniacid){
            return $this->where('uniacid', $this->uniacid) ->where('type', $type) ->column('goods_id');
        }else{
            return $this ->where('type', $type) ->column('goods_id');
        }

    }


    /*获取所有商品的商品ID*/
    public function getAllGoodIds($uniacid){
        if($uniacid){
            return $this->where('uniacid', $this->uniacid) ->column('goods_id');
        }

    }
}