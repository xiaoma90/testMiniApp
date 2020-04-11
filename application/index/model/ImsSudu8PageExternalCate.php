<?php
namespace app\index\model;
use think\Model;
use think\Db;

class ImsSudu8PageExternalCate extends Model
{
    protected $pk = 'id';
    protected $uniacid = '';
    protected $name = 'wd_xcx_external_cate';

    //初始化
    protected function initialize()
    {
        $this->uniacid = input('appletid');
    }


    //分类表查询
    public function getExternal(){
        return $this->with('pddCate,jdCate')
                ->where('uniacid', $this->uniacid)
                ->order('id desc')
                ->paginate(10,false,[ 'query' => array('appletid'=>$this->uniacid)]);
    }

    //关联拼多多分类
    public function pddCate(){
        return $this->hasMany('ImsSudu8PagePddCate','cat_id', 'pdd_cat_id');
    }


    //关联京东分类
    public function jdCate(){
        return $this->hasMany('ImsSudu8PageJdCate','cat_id', 'jd_cat_id');
    }

}