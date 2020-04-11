<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;


class Home extends Controller
{
    
    public function index(){

		$sbase = "";
		$sbase =  Db::name('wd_xcx_system_base')->find();  

		//小程序常见问题

		$news1 = Db::name('wd_xcx_system_news')->where("cate",1)->limit(3)->select();  
		if($news1){
			foreach ($news1 as &$res) {
				$res['creattime'] = date("Y-m-d",$res['creattime']);
			}
		}

		// 小程序运营干货

		$news2 = Db::name('wd_xcx_system_news')->where("cate",2)->limit(3)->select();  
		if($news2){
			foreach ($news2 as &$res) {
				$res['creattime'] = date("Y-m-d",$res['creattime']);
			}
		}
		// 小程序快讯

		$news3 = Db::name('wd_xcx_system_news')->where("cate",3)->limit(3)->select(); 
		if($news3){
			foreach ($news3 as &$res) {
				$res['creattime'] = date("Y-m-d",$res['creattime']);
			}
		}
		$this->assign('news1',$news1);
		$this->assign('news2',$news2);
		$this->assign('news3',$news3);
		$this->assign('sbase',$sbase);

        return $this->fetch('index');

    }


}