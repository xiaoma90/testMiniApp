<?php

namespace app\home\controller;

use think\Controller;

use think\Db;

use think\Request;

use think\Session;

use think\View;





class Listnews extends Controller

{

    

    public function index(){



		$cate = input("cate");



		$sbase = "";

		$sbase =  Db::name('wd_xcx_system_base')->find();  

		$this->assign('sbase',$sbase);



		if($cate){



			$cates = Db::name('wd_xcx_system_cate')->where("id",$cate)->find(); 







			$news = Db::name('wd_xcx_system_news')->where("cate",$cate)->paginate(10,false,[ 'query' => array('cate'=>input("cate"))]);

			$count = Db::name('wd_xcx_system_news')->where("cate",$cate)->count();



			if($news){

				foreach ($news as &$res) {

					$res['creattime'] = date("Y-m-d",$res['creattime']);

				}

			}



		}else{

			$this->error("没有找到对应的栏目！");

		}

		

		$newnews = Db::name('wd_xcx_system_news')->limit(5)->select();  

		

		$this->assign('news',$news);

		$this->assign('cates',$cates);

		$this->assign('newnews',$newnews);

		$this->assign('counts',$count);



        return $this->fetch('index');



    }





}

