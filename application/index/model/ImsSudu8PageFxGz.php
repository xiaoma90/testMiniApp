<?php
namespace app\index\model;
use think\Model;
use think\Db;

class ImsSudu8PageFxGz extends Model
{
    protected $pk = 'id';
    protected $uniacid = '';
    protected $name = 'wd_xcx_fx_gz';

    //初始化
    protected function initialize()
    {
        $this->uniacid = input('appletid');
    }



}