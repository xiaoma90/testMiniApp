<?php
namespace app\comhome\controller;

use think\Controller;

use think\Db;

use think\Request;

use think\Session;

use think\View;

class Solution extends Controller
{

	public function index(){

		$list = Db::name("wd_xcx_com_solution")->where("flag",1)->order("num desc,id desc")->paginate(20);

		$this->assign("lists",$list);
		$this->assign("page",4);
		$this->assign("list",$list->toArray()['data']);
		$sbase =  Db::name('wd_xcx_com_about')->find();  
		$sbase['banner'] = unserialize($sbase['banner']);
		$sbase['bannernum'] = 0;
		if($sbase['banner']['banner1'] != ""){
			$sbase['bannernum'] += 1;
		}
		if($sbase['banner']['banner2'] != ""){
			$sbase['bannernum'] += 1;
		}
		if($sbase['banner']['banner3'] != ""){
			$sbase['bannernum'] += 1;
		}
		$this->assign('sbase',$sbase);
		$register_flag = Db::name('wd_xcx_register')->value("flag");  

		$register_flag = intval($register_flag) > 0 ? $register_flag : 2;

		$this->assign('register_flag',$register_flag);
		return $this->fetch("index");

	}

	public function show(){

		$id = input("id");

		$info = Db::name("wd_xcx_com_solution")->where("id",$id)->find();
		if($info['slides']){
			$info['slides'] = unserialize($info['slides']);
		}
		if($info['typedesc']){
			$info['typedesc'] = unserialize($info['typedesc']);
		}

		$list = Db::name("wd_xcx_com_solution")->where("id",'neq',$id)->limit(3)->select();
		$this->assign("page",4);
		$this->assign("list",$list);

		$this->assign("info",$info);
		$sbase =  Db::name('wd_xcx_com_about')->find();  
		$sbase['banner'] = unserialize($sbase['banner']);
		$sbase['bannernum'] = 0;
		if($sbase['banner']['banner1'] != ""){
			$sbase['bannernum'] += 1;
		}
		if($sbase['banner']['banner2'] != ""){
			$sbase['bannernum'] += 1;
		}
		if($sbase['banner']['banner3'] != ""){
			$sbase['bannernum'] += 1;
		}
		$this->assign('sbase',$sbase);
		$register_flag = Db::name('wd_xcx_register')->value("flag");  

		$register_flag = intval($register_flag) > 0 ? $register_flag : 2;

		$this->assign('register_flag',$register_flag);
		return $this->fetch("show");

	}
	public function addHits(){
		$id = input("id");
		$is = Db::name("wd_xcx_com_solution")->where("id",$id)->field("hits")->find();
		$hits = $is['hits'] + 1;
		$res = Db::name("wd_xcx_com_solution")->where("id",$id)->update(array("hits" => $hits));
		if($res){
			return 1;
		}
	}
}