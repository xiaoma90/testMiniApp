<?php
namespace app\index\model;
use think\Model;
use think\Db;


class ImsSudu8PagePtTx extends Model{
    protected $pk = 'id';
    protected $uniacid = '';
    protected $name = 'wd_xcx_pt_tx';

    protected function initialize()
    {
        $this ->uniacid = input('appletid');
    }

}