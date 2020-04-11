<?php
namespace app\index\model;
use think\Model;

class ProductsUrl extends Model{
	protected $pk = 'id';
	protected $uniacid = '';
	protected $name = 'wd_xcx_products_url';

	protected function initialize()
    {
        $this ->uniacid = input('appletid');
    }

    

}