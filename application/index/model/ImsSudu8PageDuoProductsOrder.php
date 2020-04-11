<?php
namespace app\index\model;
use think\Model;
use think\Db;

class ImsSudu8PageDuoProductsOrder extends Model
{
    protected $pk = 'id';
    protected $uniacid = '';
    protected $name = 'wd_xcx_duo_products_order';

    //初始化
    protected function initialize()
    {
        $this->uniacid = input('appletid');
    }
}