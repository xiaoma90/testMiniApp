<?php
namespace app\index\model;
use think\Model;
use think\Db;


class ImsSudu8PageSignCon extends Model
{
    protected $pk = 'id';
    protected $uniacid = '';
    protected $name = 'wd_xcx_sign_con';

    protected function initialize()
    {
        $this->uniacid = input('appletid');
    }
    public function getSet()
    {
    	return $this->where('uniacid', $this->uniacid)
    				->find();
    }

}