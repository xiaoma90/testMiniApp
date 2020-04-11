<?php
namespace app\index\model;
use think\Model;
use think\Db;

class ImsSudu8PageStaffset extends Model
{
    protected $pk = 'id';
    protected $uniacid = '';
    protected $name = 'wd_xcx_staffset';

    //初始化
    protected function initialize()
    {
        $this->uniacid = input('appletid');
    }
}