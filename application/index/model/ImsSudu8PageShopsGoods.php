<?php
namespace app\index\model;
use think\Model;
use think\Db;


class ImsSudu8PageShopsGoods extends Model
{
    protected $pk = 'id';
    protected $uniacid = '';
    protected $name = 'wd_xcx_shops_goods';

    protected function initialize()
    {
        $this->uniacid = input('appletid');
    }
}