<?php

namespace app\comadmin\controller;



use think\Controller;

use think\Db;

use think\Request;

use think\Session;

use think\View;



class About extends Controller

{

    //关于我们操作开始

    public function index(){



        if(check_login()){

            $item = Db::name("wd_xcx_com_about")->field("descs,teamdesc")->find();

            $this->assign("newsinfo",$item);

            return $this->fetch('index');

        }else{

            $this->redirect('Index/Login/index');

        }

    }





    public function save(){

        $data = array();

       

        //公司简介

        $descs = input('descs');

        if($descs){

            $data['descs'] = $descs;

        }



        //团队简介

        $teamdesc = input("teamdesc");

        if($teamdesc){

            $data['teamdesc'] = $teamdesc;

        }





        $is = Db::name('wd_xcx_com_about')->find();

        if($is){

            $res = Db::name('wd_xcx_com_about')->where("id",1)->update($data);

        }else{

            $res = Db::name('wd_xcx_com_about')->insert($data);

        }



        if($res){

           $this->success('更新成功！');



        }else{



          $this->error('更新失败，没有修改项！');



          exit;



        }



    }



    //基础信息操作开始

    public function base(){



        if(check_login()){

            $item = Db::name("wd_xcx_com_about")->find();

            if($item['banner']){

                $item['banner'] = unserialize($item['banner']);

                $item['banner1'] = $item['banner']['banner1'];

                $item['banner2'] = $item['banner']['banner2'];

                $item['banner3'] = $item['banner']['banner3'];

                $item['banner1_t1'] = $item['banner']['banner1_t1'];

                $item['banner1_t2'] = $item['banner']['banner1_t2'];

                $item['banner2_t1'] = $item['banner']['banner2_t1'];

                $item['banner2_t2'] = $item['banner']['banner2_t2'];

                $item['banner3_t1'] = $item['banner']['banner3_t1'];

                $item['banner3_t2'] = $item['banner']['banner3_t2'];

            }else{

                $item['banner1'] = '';

                $item['banner2'] = '';

                $item['banner3'] = '';

                $item['banner1_t1'] = '';

                $item['banner1_t2'] = '';

                $item['banner2_t1'] = '';

                $item['banner2_t2'] = '';

                $item['banner3_t1'] = '';

                $item['banner3_t2'] = '';

            }

            $this->assign("newsinfo",$item);

            return $this->fetch('base');

        }else{

            $this->redirect('Index/Login/index');

        }

    }

    public function basesave(){

        $data = array();



        //系统名称

        $name = input('name');

        if($name){

            $data['name'] = $name;

        }



        //logo

        $logo = input("commonuploadpic1");

        if($logo){

            $data['logo'] = $logo;

        }



        //banner

        $banner = [];

        $banner['banner1'] = input("commonuploadpic2");

        if(!$banner['banner1']){

           $banner['banner1'] = input("tbanner1") ;

        }

        $banner['banner2'] = input("commonuploadpic3");

        if(!$banner['banner2']){

           $banner['banner2'] = input("tbanner2") ;

        }

        $banner['banner3'] = input("commonuploadpic4");

        if(!$banner['banner3']){

           $banner['banner3'] = input("tbanner3") ;

        }

        $banner['banner1_t1'] = input("banner1_t1");

        $banner['banner2_t1'] = input("banner2_t1");

        $banner['banner3_t1'] = input("banner3_t1");

        $banner['banner1_t2'] = input("banner1_t2");

        $banner['banner2_t2'] = input("banner2_t2");

        $banner['banner3_t2'] = input("banner3_t2");

        $data['banner'] = serialize($banner);



        



        //400电话 首页底部和侧边的电话、关于我们的咨询热线和售前咨询

        $hotline = input('hotline');



        if($hotline){



            $data['hotline'] = $hotline;



        }



        //客服电话：（关于我们的售后客服）

        $after_sale = input('after_sale');



        if($after_sale){



            $data['after_sale'] = $after_sale;



        }



        //企业邮箱：（关于我们的邮箱地址）

        $email = input('email');



        if($email){



            $data['email'] = $email;



        }



        //客服QQ：（首页底部和侧边的QQ、关于我们的客服QQ）

        $qq = input('qq');

        



        if($qq){



            $data['qq'] = $qq;



        }



        //微信二维码：（首页底部和侧边的二维码）

        $ewm = input('commonuploadpic5');



        if($ewm){



            $data['ewm'] = $ewm;



        }



        //公司地址：（关于我们）

        $address = input('address');



        if($address){



            $data['address'] = $address;



        }



        //坐标：（关于我们）

        $letlon = input("letlon");

        if($letlon){



            $data['letlon'] = $letlon;



        }



        //首页备案信息

        $copyright = input("copyright");

        if($copyright){



            $data['copyright'] = $copyright;



        }



        $is = Db::name('wd_xcx_com_about')->find();

        if($is){

            $res = Db::name('wd_xcx_com_about')->where("id",1)->update($data);

        }else{

            $res = Db::name('wd_xcx_com_about')->insert($data);

        }



        if($res){

           $this->success('更新成功！');



        }else{



          $this->error('更新失败，没有修改项！');



          exit;



        }



    }



    //员工列表操作开始

    public function lists(){
        if(check_login()){
            $list = Db::name('wd_xcx_com_staff')->order("num desc,id desc")->paginate(10);

            $count = Db::name('wd_xcx_com_staff')->count();

            $this->assign("list",$list);

            $this->assign("count",$count);

            return $this->fetch("lists");
        }else{
            $this->redirect('Index/Login/index');
        }
    }

    public function add(){

        if(check_login()){

            $newsid = input("newsid");

            if($newsid){

               $item = Db::name('wd_xcx_com_staff')->where("id",$newsid)->find();

            }else{

                $item = "";

            }

            $this->assign('newsid',$newsid);

            $this->assign('newsinfo',$item);

            return $this->fetch('add');

        }else{

            $this->redirect('Index/login/index');

        }

    }

    public function addsave(){

        $data['num'] = input("num");

        $data['flag'] = input("flag");

        $data['name'] = input("name");

        $data['pic'] = input("commonuploadpic1");

        $data['position'] = input("position");

        $newsid = input("newsid");

        if($newsid){

            $res = Db::name('wd_xcx_com_staff')->where("id",$newsid)->update($data);

        }else{

            $data['createtime'] = time();

            $res = Db::name('wd_xcx_com_staff')->insert($data);

        }

        if($res){

            $this->success("员工信息更新成功", Url('About/lists'));

        }else{

            $this->error("员工信息更新失败，没有修改项");

        }

    }

    public function del(){

       $newsid = input("newsid");

        if($newsid){

           $res = Db::name('wd_xcx_com_staff')->where("id",$newsid)->delete();

           if($res){

               $this->success('删除成功');

            }else{

              $this->error('删除失败');

              exit;

            }

        } 

    }

}