<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;


class Cprinter extends Base
{


    public function index(){

        if(check_login()){
        	if(powerget()){

        		$id = input("appletid");
        		$res = Db::name('wd_xcx_applet')->where("id",$id)->find();

                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
        		$this->assign('applet',$res);
                $bases = Db::name('wd_xcx_food_printer')->where("uniacid",$id)->find();
        		$this->assign('bases',$bases);
        	}else{
        		$usergroup = Session::get('usergroup');
        		if($usergroup==1){
        			$this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/applet');
        		}
        		if($usergroup==2){
        			$this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
        		}
                if($usergroup==3){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
                }
        	}
            return $this->fetch('index');
        }else{
            $this->redirect('Login/index');
        }
    }



    public function save(){
        $appletid = input("appletid");
        $data = array();
        $data['status'] = $_POST['status'];
        $pname = $_POST['pname'];
        if($pname){
        	$data['pname'] = $pname;
        }else{
            $this->error('打印机名称不能为空！');
            exit;
        }

        $title = $_POST['title'];
        if($title){
            $data['title'] = $title;
        }else{
            $this->error('头部标题不能为空！');
            exit;
        }

        $models = $_POST['models'];
        if($models){
            $data['models'] = $models;
        }else{
            $this->error('请选择打印机型号！');
            exit;
        }

        $nid = $_POST['nid'];
        if($nid){
            $data['nid'] = trim($nid);
        }else{
            $this->error('打印机终端号不能为空！');
            exit;
        }

        $nkey = $_POST['nkey'];
        if($nkey){
            $data['nkey'] = trim($nkey);
        }else{
            $this->error('终端号秘钥不能为空！');
            exit;
        }

        $uid = $_POST['uid'];
        if($uid){
            $data['uid'] = trim($uid);
        }else{
            $this->error('用户id不能为空！');
            exit;
        }

        $apikey = $_POST['apikey'];
        if($apikey){
            $data['apikey'] = trim($apikey);
        }else{
            $this->error('秘钥不能为空！');
            exit;
        }

        $data['createtime'] = time();
       
        $bases = Db::name('wd_xcx_food_printer')->where("uniacid",$appletid)->count();
        if($bases>0){
        	$res = Db::name('wd_xcx_food_printer')->where("uniacid",$appletid)->update($data);
        }else{
        	$data['uniacid'] = $appletid;
        	$res = Db::name('wd_xcx_food_printer')->insert($data);
        }

        if($res){
          $this->success('打印机基本配置更新成功！');
        }else{
          $this->error('打印机基本配置更新失败，没有修改项！');
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

    //上传成功后获取图片
    public function getimg(){
    	$id = $_POST['id'];  	
    	$allimg = Db::name('wd_xcx_image_url')->where("appletid",$id)->select();
    	if($allimg){
    		return $allimg;
    	}
		
    }

    public function del(){
        $id = input("id");
        $res = Db::name('wd_xcx_image_url')->where('id', $id)->delete();
        if($res){
            return 1;
        }else{
            $this->error("删除失败！");
        }
    }

}