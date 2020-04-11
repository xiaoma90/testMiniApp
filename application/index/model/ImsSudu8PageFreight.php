<?php
namespace app\index\model;
use think\Model;
use think\Db;


class ImsSudu8PageFreight extends Model{
    protected $pk = 'id';
    protected $uniacid = '';
    protected $name = 'wd_xcx_freight';

    protected function initialize()
    {
        $this ->uniacid = input('appletid');
    }

}