<?php

namespace app\index\controller;

use think\Controller;

use think\Db;

use think\Request;

use think\Session;

use think\View;





class User extends Controller

{

    public function index(){



        if(check_login()){
        	$userinfo = Db::name('wd_xcx_admin')->where("uid",Session::get('uid'))->find();
   			$this->assign('userinfo',$userinfo);

            return $this->fetch('index');
        }else{

            $this->redirect('Login/index');

        }

        

    }





    public function save(){



        //头像

        $icon = $this->onepic_uploade("icon");

        if($icon){

            $data['icon'] = $icon;

        }

        //真实名字

        $realname = $_POST['realname'];

        if($realname){

            $data['realname'] = $realname;

        }

        //手机

        $mobile = $_POST['mobile'];

        if($mobile){

            $data['mobile'] = $mobile;

        }

        //email

        $email = $_POST['email'];

        if($email){

            $data['email'] = $email;

        }



        //password

        $password = $_POST['password'];

        if($password){

            $data['password'] = md5($password);

        }



        $uid = Session::get('uid');



        $res = Db::name('wd_xcx_admin')->where("uid",$uid)->update($data);

        if($res){

          $this->success('用户信息更新成功！');

        }else{

          $this->error('用户信息更新失败，没有更新项目！');

          exit;

        }

    }





     //单个图片上传操作

    function onepic_uploade($file){

        $thumb = request()->file($file);

        if(isset($thumb)){

            $dir = upload_img();

            $info = $thumb->validate(['ext'=>'jpg,png,gif,jpeg'])->move($dir); 

            if($info){  

                $imgurl = ROOT_HOST."/upimages/".date("Ymd",time())."/".$info->getFilename();

                return $imgurl;

            }  

        }

    }

   







}