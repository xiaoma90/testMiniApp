<?php
namespace app\index\model;
use think\Model;
use think\Db;


class ImsSudu8PageStore extends Model
{
    protected $pk = 'id';
    protected $uniacid = '';
    protected $name = 'wd_xcx_store';

    protected function initialize()
    {
        $this->uniacid = input('appletid');
    }

}