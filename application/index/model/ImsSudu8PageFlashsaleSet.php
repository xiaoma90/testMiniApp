<?php
namespace app\index\model;
use think\Model;
use think\Db;

class ImsSudu8PageFlashsaleSet extends Model
{
    protected $pk = 'id';
    protected $uniacid = '';
    protected $name = 'wd_xcx_flashsale_set';

    //初始化
    protected function initialize()
    {
        $this->uniacid = input('appletid');
    }

    //获取当前项目秒杀商品基础设置
    public function getBaseSet(){
        return $this->where('uniacid', $this->uniacid) ->find();
    }
}