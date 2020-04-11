<?php
namespace app\index\model;
use think\Model;

class ImsSudu8PageFlashsaleCate extends Model{
	protected $pk = 'id';
	protected $uniacid = '';
	protected $name = 'wd_xcx_flashsale_cate';

	protected function initialize()
    {
        $this ->uniacid = input('appletid');
    }

	//获取分类的子类  带分页
	public function getAllCate($cid = 0){
		$allCate = $this ->where('uniacid', $this->uniacid)
					->where('cid', $cid)
        			->where('catefor', 'flashsale')
        			->order('num desc, id desc')
        			->paginate(10, false, [ 'query' => array('appletid'=>$this->uniacid)]);
        return $allCate;
	}

	//获取子类分类数量
	public function getChildCateCount($cid = 0){
		return $this ->where('uniacid', $this->uniacid)
					->where('cid', $cid)
					->where('catefor', 'flashsale')
					->count();
	}

	//获取所有分类
	public function getCates($cid = 0){
		return $this ->where('uniacid', $this->uniacid)
					->where('cid', $cid)
					->where('catefor', 'flashsale')
					->select();
	}

	//获取指定栏目信息
	public function getCateById($id){
		return $this ->where('uniacid', $this->uniacid)
					->where('id', $id)
					->find();
	}


	//获取指定分类的子类
	public function getChildCate($cid){
		return $this ->where('uniacid', $this->uniacid)
					->where('cid', $cid)
					->where('catefor', 'flashsale')
					->select();
	}


	/* 预约预定分类开始 */
	//获取分类的子类  带分页
	public function getReserveAllCate($cid = 0){
		$allCate = $this ->where('uniacid', $this->uniacid)
					->where('cid', $cid)
        			->where('catefor', 'reserve')
        			->order('num desc, id desc')
        			->paginate(10, false, [ 'query' => array('appletid'=>$this->uniacid)]);
        return $allCate;
	}

	//获取子类分类数量
	public function getReserveChildCateCount($cid = 0){
		return $this ->where('uniacid', $this->uniacid)
					->where('cid', $cid)
					->where('catefor', 'reserve')
					->count();
	}

	//获取所有分类
	public function getReserveCates($cid = 0){
		return $this ->where('uniacid', $this->uniacid)
					->where('cid', $cid)
					->where('catefor', 'reserve')
					->select();
	}

	//获取指定栏目信息
	public function getReserveCateById($id){
		return $this ->where('uniacid', $this->uniacid)
					->where('id', $id)
					->find();
	}


	//获取指定分类的子类
	public function getReserveChildCate($cid){
		return $this ->where('uniacid', $this->uniacid)
					->where('cid', $cid)
					->where('catefor', 'reserve')
					->select();
	}
	

	//关联product_url表
	public function slide(){
		return $this->hasMany('ProductsUrl','randid', 'randid');
	}


}
