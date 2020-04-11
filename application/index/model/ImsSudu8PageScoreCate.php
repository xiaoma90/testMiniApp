<?php
namespace app\index\model;
use think\Model;
use think\Db;


class ImsSudu8PageScoreCate extends Model
{
    protected $pk = 'id';
    protected $uniacid = '';
    protected $name = 'wd_xcx_score_cate';

    protected function initialize()
    {
        $this->uniacid = input('appletid');
    }

    public function getCates(){
    	return $this->where('uniacid', $this->uniacid)
    				->order("num desc")
    				->select();
    }

}