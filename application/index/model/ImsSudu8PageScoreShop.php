<?php
namespace app\index\model;
use think\Model;
use think\Db;


class ImsSudu8PageScoreShop extends Model
{
    protected $pk = 'id';
    protected $uniacid = '';
    protected $name = 'wd_xcx_score_shop';

    protected function initialize()
    {
        $this->uniacid = input('appletid');
    }

    public function getPros($where){
    	return $this->where($where)
                    ->where('uniacid',$this->uniacid)
                    ->order('num desc, id desc')
                    ->paginate(10, false, [ 'query' => array('appletid'=>$this->uniacid)]);
    }

    public function cateTable(){
        return $this->hasOne('ImsSudu8PageScoreCate','id', 'cid');
    }

}