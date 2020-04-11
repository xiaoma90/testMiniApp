<?php
namespace app\index\model;
use think\Model;
use think\Db;

class ImsSudu8PageScore extends Model
{
    protected $pk = 'id';
    protected $uniacid = '';
    protected $name = 'wd_xcx_score';

    //初始化
    protected function initialize()
    {
        $this->uniacid = input('appletid');
    }
}