<?php
namespace app\index\model;
use think\Model;
use think\Db;

class ImsSudu8PagePddCate extends Model
{
    protected $pk = 'id';
    protected $uniacid = '';
    protected $name = 'wd_xcx_pdd_cate';

    //初始化
    protected function initialize()
    {
        $this->uniacid = input('appletid');
    }

}
