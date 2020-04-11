<?php
namespace app\index\model;
use think\Model;
use think\Db;


class ImsSudu8PageProducts extends Model{
	protected $pk = 'id';
	protected $uniacid = '';
    protected $name = 'wd_xcx_products';

	protected function initialize()
    {
        $this ->uniacid = input('appletid');
    }

    //按分类
    protected function scopeGetByCid($query, $cidarr=[], $cid = 0){
    	if(is_array($cidarr) && $cid != 0){
    		return $query ->where('cid', 'in', $cidarr);
    	}
    	
    }

    //按标题搜索
    protected function scopeGetByTitle($query, $title = false){
    	if($title){
    		return $query ->where('title', 'like', '%'.$title.'%');
    	}
    }

    //查询秒杀商品
    public function scopeGetGoods($query, $cid, $title){
    	return $query ->where('uniacid', $this->uniacid) ->where('type', 'showPro')
            ->where('is_more', 0)
            ->order('num desc');
    }

    //查询预约商品
    public function scopeGetReserveGoods($query, $cid, $title){
        return $query ->where('uniacid', $this->uniacid) ->where('type', 'reserve')
            ->where('is_more', 1)
            ->order('num desc');
    }

    //一对多 商品幻灯片
    public function slide(){
        return $this->hasMany('ProductsUrl','randid', 'randid');
    }






}
