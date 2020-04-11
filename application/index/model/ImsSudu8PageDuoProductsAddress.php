<?php
namespace app\index\model;
use think\Model;

class ImsSudu8PageDuoProductsAddress extends Model{
    protected $pk = 'id';
    protected $uniacid = '';
    protected $name = 'wd_xcx_duo_products_address';

    protected function initialize()
    {
        $this ->uniacid = input('appletid');
    }



}