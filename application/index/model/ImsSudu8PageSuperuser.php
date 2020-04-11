<?php
namespace app\index\model;
use think\Model;
use think\Db;

class ImsSudu8PageSuperuser extends Model
{
    protected $pk = 'id';
    protected $uniacid = '';
    protected $name = 'wd_xcx_superuser';

    //初始化
    protected function initialize()
    {
        $this->uniacid = input('appletid');
    }
    public function getScore($suid){
    	return $this->where("id", $suid)
           	        ->field('score')
           	        ->value('score');
    }
}