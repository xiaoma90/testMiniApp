<?php
namespace app\index\model;
use think\Model;
use think\Db;


class ImsSudu8PageUser extends Model
{
    protected $pk = 'id';
    protected $uniacid = '';
    protected $name = 'wd_xcx_user';

    protected function initialize()
    {
        $this->uniacid = input('appletid');
    }

}